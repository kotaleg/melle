<?php
class ModelApiImport1C extends Model
{
    private $codename = 'import_1c';
    private $route = 'api/import_1c';
    private $setting_route = 'extension/module/import_1c';

    private $exchange_path;

    const OFFERS_GROUP = '1c_offers_group';
    const OFFERS = '1c_offers';

    function __construct($registry)
    {
        parent::__construct($registry);

        $this->import_1c = new \import_1c\import_1c();
        $this->exchange_path = $this->getRootPath() . 'protected/runtime/exchange/';

        $this->files = array(
            'import.xml',
            'offers.xml',
        );
    }

    public function test()
    {
        // $this->import_1c->openFile("{$this->exchange_path}import.xml");
        $this->import_1c->openFile("{$this->exchange_path}offers.xml");
        $this->import_1c->test();
    }

    private function getRootPath()
    {
        return dirname(DIR_SYSTEM).'/';
    }

    private function createTables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX .$this->db->escape(self::OFFERS_GROUP) ."` (
            `_id` int(11) NOT NULL AUTO_INCREMENT,

            `c_id` varchar(255) NOT NULL,
            `name` varchar(64) NOT NULL,

            PRIMARY KEY (`_id`),
            UNIQUE KEY `no_duplicate` (`c_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX .$this->db->escape(self::OFFERS) ."` (
            `_id` int(11) NOT NULL AUTO_INCREMENT,

            `c_id` varchar(255) NOT NULL,
            `name` varchar(64) NOT NULL,

            PRIMARY KEY (`_id`),
            UNIQUE KEY `no_duplicate` (`c_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");
    }

    private function disableAllProducts()
    {
        //
    }

    private function disableAllCategories()
    {
        //
    }
}
