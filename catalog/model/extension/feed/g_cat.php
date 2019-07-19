<?php
/*
 *  location: catalog/model
 */
class ModelExtensionFeedGCat extends Model
{
    private $codename = 'g_cat';
    private $route = 'extension/feed/g_cat';

    const G_CATEGORY = 'gcat_category';
    const G_CATEGORY_DESCRIPTION = 'gcat_category_description';
    const G_CONNECTION = 'gcat_connection';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/setting');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/db');
    }

    public function getCategoryIdFor($storeCategoryId)
    {
        $q = $this->db->query("SELECT `categoryId`
            FROM `". DB_PREFIX . self::G_CONNECTION . "`
            WHERE `storeCategoryId` = '" . (int) $storeCategoryId . "'");

        if ($q->num_rows) {
            return (int) $q->row['categoryId'];
        }
    }

}