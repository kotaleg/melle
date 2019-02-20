<?php
/*
 *  location: admin/model
 */
class ModelExtensionTotalProDiscount extends Model
{
    private $codename = 'pro_discount';
    private $route = 'extension/total/pro_discount';

    const DISCOUNT_TABLE = 'pd_discounts';
    const CATEGORY_TABLE = 'pd_category';
    const PRODUCT_TABLE = 'pd_product';
    const MANUFACTURER_TABLE = 'pd_manufacturer';
    const CUSTOMER_TABLE = 'pd_customer';

    const SALE = 'sale';
    const SALE_COUNT = 'sale_count';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/setting');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/db');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);
    }

    public function createTables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . $this->db->escape(self::DISCOUNT_TABLE) ."` (
            `discount_id` int(11) NOT NULL AUTO_INCREMENT,
            `type` varchar(255) NOT NULL,
            `progress` decimal(15,4) NOT NULL,
            `description` TEXT NOT NULL,
            `start_date` datetime NOT NULL,
            `finish_date` datetime NOT NULL,
            PRIMARY KEY (`discount_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . $this->db->escape(self::CATEGORY_TABLE) ."` (
            `discount_id` int(11) NOT NULL,
            `category_id` int(11) NOT NULL,
            PRIMARY KEY (`discount_id`,`category_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . $this->db->escape(self::MANUFACTURER_TABLE) ."` (
            `discount_id` int(11) NOT NULL,
            `manufacturer_id` int(11) NOT NULL,
            PRIMARY KEY (`discount_id`,`manufacturer_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . $this->db->escape(self::PRODUCT_TABLE) ."` (
            `discount_id` int(11) NOT NULL,
            `product_id` int(11) NOT NULL,
            PRIMARY KEY (`discount_id`,`product_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . $this->db->escape(self::CUSTOMER_TABLE) ."` (
            `discount_id` int(11) NOT NULL,
            `customer_id` int(11) NOT NULL,
            PRIMARY KEY (`discount_id`,`customer_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");
    }

    public function dropTables()
    {
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . $this->db->escape(self::DISCOUNT_TABLE) ."`");
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . $this->db->escape(self::CATEGORY_TABLE) ."`");
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . $this->db->escape(self::MANUFACTURER_TABLE) ."`");
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . $this->db->escape(self::CUSTOMER_TABLE) ."`");
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . $this->db->escape(self::PRODUCT_TABLE) ."`");
    }

    public function getScriptFiles()
    {
        if (isset($this->setting['debug']) && $this->setting['debug']) {
            $rand = '?'.rand(777, 999);
        } else { $rand = ''; }

        $scripts = array();
        $scripts[] = "view/javascript/{$this->codename}/dist/{$this->codename}.js{$rand}";

        return $scripts;
    }


}