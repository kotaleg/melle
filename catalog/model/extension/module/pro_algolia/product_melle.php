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
            return utf8_strtolower($row['altName']);
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

        return array(
            'objectID' => \pro_algolia\id::generateIdForProduct((int) $productId),
            'productId' => (int) $productId,
            
            'name' => $productData['name'],
            'h1' => $h1,
            'description' => html_entity_decode($productData['description'], ENT_QUOTES, 'UTF-8'),
            'smallDescription' => html_entity_decode($smallDescription, ENT_QUOTES, 'UTF-8'),
            'extraDescription' => html_entity_decode($extraDescription, ENT_QUOTES, 'UTF-8'),
            'productAltNames' => $productAltNames,

            'manufacturer' => $manufacturer,
            'manufacturerAltNames' => $manufacturerAltNames,

            'image' => $image,

            'price' => $price,
            'special' => $special,
            'specialText' => $specialText,
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
}