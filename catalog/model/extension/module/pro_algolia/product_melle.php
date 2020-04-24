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

        if (!is_file(DIR_IMAGE.$productData['image'])) {
            $productData['image'] = 'no_image.png';
        }
        $image = $this->model_tool_image->resize(
            $productData['image'], 
            $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), 
            $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'), 
            true
        );

        $h1 = (isset($productData['h1'])) ? $productData['h1'] : '';
        $smallDescription = (isset($productData['small_description'])) ? $productData['small_description'] : '';

        /* EXTRA DESCRIPTION START */
        $this->load->model('extension/module/extra_description');
        $extraDescription = $this->model_extension_module_extra_description
            ->getDescription($productId);
        /* EXTRA DESCRIPTION END */

        return array(
            'objectID' => \pro_algolia\id::generateIdForProduct($productData['import_id']),
            'productId' => (int) $productId,
            'name' => $productData['name'],
            'h1' => $h1,
            'description' => html_entity_decode($productData['description'], ENT_QUOTES, 'UTF-8'),
            'smallDescription' => html_entity_decode($smallDescription, ENT_QUOTES, 'UTF-8'),
            'extraDescription' => html_entity_decode($extraDescription, ENT_QUOTES, 'UTF-8'),
            'manufacturer' => $productData['manufacturer'],
            'image' => $image,
        );
    }

}