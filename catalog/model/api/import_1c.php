<?php
class ModelApiImport1C extends Model
{
    private $codename = 'import_1c';
    private $route = 'api/import_1c';
    private $setting_route = 'extension/module/import_1c';

    private $exchange_path;

    const OFFERS_GROUP = '1c_offers_group';
    const OFFERS = '1c_offers';

    const PRODUCT_TABLE = 'product';
    const CATEGORY_TABLE = 'category';
    const MANUFACTURER_TABLE = 'manufacturer';
    const ATTRIBUTE_TABLE = 'attribute';
    const ATTRIBUTE_GROUP_TABLE = 'attribute_group';

    const IMPORT_FIELD = 'import_id';

    function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/db');
        $this->load->model('extension/module/super_offers');

        $this->import_1c = new \import_1c\import_1c();
        $this->exchange_path = $this->getRootPath() . 'protected/runtime/exchange/';

        $this->files = array(
            'import*.xml',
            'offers*.xml',
        );
    }

    public function actionCatalogInit()
    {
        // if (is_dir($this->exchange_path)) {
        //     $this->import_1c->clearDir("{$this->exchange_path}", array(
        //         '*.gitignore',
        //         '*/import_files/*.jpg',
        //         '*/import_files/*.png',
        //     ));
        // }

        $tables = array(
            self::PRODUCT_TABLE,
            self::CATEGORY_TABLE,
            self::MANUFACTURER_TABLE,
        );

        // ADD IMPORT FIELD ID TO THE TABLES
        foreach ($tables as $tn) {
            if (!$this->model_extension_pro_patch_db->isColumnExist($tn, self::IMPORT_FIELD)) {
                $this->db->query("ALTER TABLE `". DB_PREFIX . $this->db->escape($tn) . "`
                    ADD COLUMN `". $this->db->escape(self::IMPORT_FIELD) . "` VARCHAR(255) NOT NULL;");
            }
        }
    }

    public function actionCatalogFile($filename)
    {
        $json = array();
        if (empty($filename)) {
            $json['error'][] = 'Невереный filename';
            return $json;
        }

        try {
            $path = "{$this->exchange_path}{$filename}";
            if (!is_dir(dirname($path))) {
                $this->import_1c->createDir(dirname($path));
            }

            if (is_file($path)) {
                unlink($path);
            }

            $in = fopen('php://input', 'rb');
            $out = fopen($path, 'a');
            while (!feof($in)) {
                fwrite($out, fread($in, 8192));
            }
            fclose($in);
            fclose($out);
            $json['success'] = true;
        } catch (\Exception $e) {
            $json['error'][] = 'Ошибка при сохраненнии файла';
        }

        return $json;
    }

    public function actionCatalogImport($filename)
    {
        $json = array();
        $json['continue'] = true;
        $json['success'] = true;

        if (empty($filename)) {
            $json['error'][] = 'Невереный filename';
            $json['continue'] = false;
            $json['success'] = false;
            return $json;
        }

        $realpath = "{$this->exchange_path}{$filename}";

        if (!is_file($realpath) || !is_readable($realpath)) {
            $json['error'][] = 'Filename не существует';
            $json['continue'] = false;
            $json['success'] = false;
            return $json;
        }

        $this->import_1c->openFile($realpath);
        $parsed = $this->import_1c->parse();

        // LANGUAGES
        $this->load->model('api/import_1c/language');
        $languages = $this->model_api_import_1c_language->getLanguages();

        // PRODUCERS
        $this->load->model('api/import_1c/producer');
        $this->model_api_import_1c_producer->action($parsed);

        // GROUP
        $this->load->model('api/import_1c/group');
        $this->model_api_import_1c_group->action('Группа', $languages);

        // DEN
        $this->load->model('api/import_1c/group');
        $this->model_api_import_1c_group->action('Ден', $languages);

        // SOSTAV
        $this->load->model('api/import_1c/group');
        $this->model_api_import_1c_group->action('Состав', $languages);

        // OPTION
        $this->load->model('api/import_1c/option');
        $this->model_api_import_1c_option->action($parsed, $languages);

        // PRODUCTS
        $this->load->model('api/import_1c/product');
        $this->model_api_import_1c_product->action($parsed, $languages);

        echo "<pre>"; print_r($parsed->classificator->options); echo "</pre>";exit;

        return $json;
    }

    public function actionCatalogDelete()
    {
        $json = array();

        $this->load->model('api/import_1c/product');
        $this->model_api_import_1c_product->deleteAllProducts();

        $json['success'] = true;
        return $json;
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
        $this->db->query("UPDATE `". DB_PREFIX ."product`
            SET `status` = '".(bool)false."'");
    }

    private function disableAllCategories()
    {
        $this->db->query("UPDATE `". DB_PREFIX ."category`
            SET `status` = '".(bool)false."'");
    }
}
