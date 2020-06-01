<?php
/*
 *  location: catalog/model
 */
class ModelExtensionModulePROAlgoliaProduct extends Model
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

    public function getId($productId)
    {
        return \pro_algolia\id::generateIdForProduct((int) $productId);
    }

    public function prepareData($productId)
    {
        $productData = $this->model_catalog_product->getProduct($productId);

        if (!isset($productData['product_id'])) {
            return null;
        }

        return array(
            'productId' => (int) $productId,

            'description' => html_entity_decode($productData['description'], ENT_QUOTES, 'UTF-8'),
            'manufacturer' => $productData['manufacturer'],

            // TODO: convert currency
            'price' => (double) $productData['price'],
            'special' => (double) $productData['special'],
        );
    }
}