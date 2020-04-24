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
    }

    public function codename()
    {
        return $this->localCodename;
    }

    public function prepareData($productId)
    {
        return array(
            'objectID' => \pro_algolia\id::generateIdForProduct($productId),
            'productId' => (int) $productId,
        );
    }
}