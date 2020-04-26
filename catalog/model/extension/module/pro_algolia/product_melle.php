<?php
/*
 *  location: catalog/model
 */
class ModelExtensionModulePROAlgoliaProductMelle extends Model
{
    private $codename = 'pro_algolia';
    private $route = 'extension/module/pro_algolia';

    private $localCodename = 'product';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/db');
        $this->load->model('catalog/product');
    }

    public function codename()
    {
        return $this->localCodename;
    }

    public function prepareData($productId)
    {
        $productData = $this->model_catalog_product->getProduct($productId);

        if (!isset($productData['import_id'])) {
            return null;
        }

        $this->load->model('tool/image');
        if (!is_file(DIR_IMAGE.$productData['image'])) {
            $productData['image'] = 'no_image.png';
        }
        $image = $this->model_tool_image->resize(
            $productData['image'], 
            $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), 
            $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'), 
            true
        );

        $h1 = (isset($productData['h1'])) ? $productData['h1'] : '';
        $smallDescription = (isset($productData['small_description'])) ? $productData['small_description'] : '';

        /* EXTRA DESCRIPTION START */
        $this->load->model('extension/module/extra_description');
        $extraDescription = $this->model_extension_module_extra_description
            ->getDescription($productId);
        /* EXTRA DESCRIPTION END */

        $manufacturer = utf8_strtolower($productData['manufacturer']);

        $manufacturerAltNames = array_map(function($row) {
            if (utf8_strlen($row['altName']) > 0) {
                return utf8_strtolower($row['altName']);
            }
        }, $this->getManufacturerAltNamesById($productData['manufacturer_id']));

        $productAltNames = $this->generateProductAltNames(
            $h1,
            $manufacturer,
            $manufacturerAltNames
        );

        $this->load->model('extension/module/super_offers');
        $defaultvalues = $this->model_extension_module_super_offers
            ->getDefaultValues($productData['product_id'], $productData);

        $price = $defaultvalues['price'];
        $special = $defaultvalues['special'];

        /* SPECIAL TEXT START */
        $this->load->model('extension/total/pro_discount');
        $specialText = $this->model_extension_total_pro_discount
            ->getSpecialText($productData['product_id'], true);
        /* SPECIAL TEXT START */

        /* NAME FOR PRINT START */
        $this->load->model('extension/module/super_offers');
        $productPrintNames = array();
        $productCombinationNames = array();

        $combinationsForProduct = $this->model_extension_module_super_offers
            ->_getCombinationsForProduct($productData['product_id']);

        foreach ($combinationsForProduct as $combinationForProduct) {
            if (isset($combinationForProduct['product_code'])
            && utf8_strlen($combinationForProduct['product_code']) > 0) {
                $productCombinationNames[] = $combinationForProduct['product_code'];
            }
            if (isset($combinationForProduct['name_for_print'])
            && utf8_strlen($combinationForProduct['name_for_print']) > 0) {
                $productPrintNames[] = $combinationForProduct['name_for_print'];
            }
        }
        /* NAME FOR PRINT END */

        $material = $this->getAttributeValue($productData['product_id'], 'материал');
        $den = $this->getAttributeValue($productData['product_id'], 'ден');

        return array(
            'objectID' => \pro_algolia\id::generateIdForProduct((int) $productId),
            'productId' => (int) $productId,
            
            'name' => $productData['name'],
            'h1' => $h1,
            'productAltNames' => $productAltNames,
            'productCombinationNames' => $productCombinationNames,
            'productPrintNames' => $productPrintNames,

            'description' => html_entity_decode($productData['description'], ENT_QUOTES, 'UTF-8'),
            'smallDescription' => html_entity_decode($smallDescription, ENT_QUOTES, 'UTF-8'),
            'extraDescription' => html_entity_decode($extraDescription, ENT_QUOTES, 'UTF-8'),

            'manufacturer' => $manufacturer,
            'manufacturerAltNames' => $manufacturerAltNames,

            'image' => $image,

            'price' => $this->getPrice($price),
            'special' => $this->getPrice($special),
            'specialText' => $specialText,

            'material' => $material,
            'den' => (int) $den,
        );
    }

    private function getManufacturerAltNamesById($manufacturerId)
    {
        return $this->db->query("SELECT * FROM `". DB_PREFIX ."manufacturer_alt_name`
            WHERE `manufacturerId` = '" . (int) $manufacturerId . "'")->rows;
    }

    private function generateProductAltNames($productName, $manufacturerName, $manufacturerAltNames)
    {
        $productAltNames = array();

        $productNameForReplace = utf8_strtolower($productName);

        foreach ($manufacturerAltNames as $manufacturerAltName) {
            if (strpos($productNameForReplace, $manufacturerName)) {
                $productAltNames[] = str_replace($manufacturerName, $manufacturerAltName, $productNameForReplace);
            }
        }

        return $productAltNames;
    }

    private function getAttributeValue($productId, $attributeName)
    {
        $this->load->model('catalog/product');
        $attributeGroups = $this->model_catalog_product
            ->getProductAttributes($productId);

        foreach ($attributeGroups as $group) {
            if (strcmp(trim(utf8_strtolower($group['name'])), utf8_strtolower('Атрибуты')) === 0) {
                foreach ($group['attribute'] as $attr) {
                    if (strcmp(trim(utf8_strtolower($attr['name'])), utf8_strtolower($attributeName)) === 0) {
                        return $attr['text'];
                    }
                }
            }
        }

        return '';
    }

    private function getPrice($price)
    {
        if (is_string($price)) {
            $price = preg_replace('/\s+/', '', (string) $price);
            return (double) $price;
        }

        if (is_numeric($price)) {
            return (double) $price;
        }

        return false;
    }
}