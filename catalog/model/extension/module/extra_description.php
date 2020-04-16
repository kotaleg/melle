<?php
/*
 *  location: admin/model
 */
class ModelExtensionModuleExtraDescription extends Model
{
    private $codename = 'extra_description';
    private $route = 'extension/module/extra_description';

    const DESCRIPTION_TABLE = 'product_extra_description';

    public function __construct($registry)
    {
        parent::__construct($registry);
    }

    public function getDescription($productId)
    {
        $row = $this->db->query("SELECT * FROM `". DB_PREFIX . $this->db->escape(self::DESCRIPTION_TABLE) . "`
            WHERE `productId` = '". (int) $productId ."'
            AND `languageId` = '" . (int) $this->config->get('config_language_id') ."'")->row;

        if (isset($row['description'])) {
            return $row['description'];
        }

        return '';
    }
}