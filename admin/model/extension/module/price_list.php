<?php
/*
 *  location: admin/model
 */
class ModelExtensionModulePriceList extends Model
{
    private $codename = 'price_list';
    private $route = 'extension/module/price_list';

    const PL_TABLE = 'pl_items';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/setting');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/db');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);
        $this->workFolder = dirname(DIR_SYSTEM).'/'.$this->setting['path'];
    }

    public function createTables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . self::PL_TABLE ."` (
            `_id` int(11) NOT NULL AUTO_INCREMENT,

            `filePath` varchar(255) NOT NULL,
            `title` varchar(255) NOT NULL,
            `downloadCount` int(11) NOT NULL,
            `sortOrder` tinyint(1) NOT NULL,
            `status` tinyint(1) NOT NULL,

            PRIMARY KEY (`_id`),
            UNIQUE KEY `no_duplicate` (`filePath`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");
    }

    public function dropTables()
    {
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . self::PL_TABLE ."`");
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

    public function getWorkFolderName()
    {
        return $this->setting['path'];
    }

    public function getPriceFiles()
    {
        $files = array();

        foreach (glob("{$this->workFolder}*{.xls,.xlsx}", GLOB_BRACE) as $file) {
            if (is_file($file)) {
                $name = str_replace($this->workFolder, '', $file);
                $files[] = $this->preparePriceFile($name);
            }
        }

        return $files;
    }

    public function getPriceFile($name)
    {
        return $this->preparePriceFile($name);
    }

    private function preparePriceFile($name)
    {
        $file = $this->getPriceListByName($name);

        if ($file) {
            $file['status'] = (bool)$file['status'];
            return $file;
        } else {
            $this->initPriceList($name);
            return $this->preparePriceFile($name);
        }
    }

    private function getPriceListByName($name)
    {
        $q = $this->db->query("SELECT *
            FROM `". DB_PREFIX . $this->db->escape(self::PL_TABLE) . "`
            WHERE `filePath` = '" . $this->db->escape($name) . "'");

        return $q->row;
    }

    private function initPriceList($name)
    {
        $this->db->query("INSERT INTO `". DB_PREFIX . $this->db->escape(self::PL_TABLE) ."`
            SET `filePath` = '". $this->db->escape($name) ."',
                `title` = '". $this->db->escape($name) ."',
                `downloadCount` = '". (int)0 ."',
                `sortOrder` = '". (int)0 ."',
                `status` = '". (bool)0 ."'");
    }

    public function savePriceList($data)
    {
        $json['saved'] = false;

        if (empty($data['_id'])) {
            $json['error'][] = 'Нет ID';
        }

        if ((utf8_strlen($data['title']) < 1) || (utf8_strlen($data['title']) > 32)) {
            $json['error'][] = 'Какое то хреновое имя';
        }

        if (!isset($json['error'])) {

            $this->db->query("UPDATE `". DB_PREFIX . self::PL_TABLE ."`
                SET `title` = '". $this->db->escape($data['title']) ."',
                    `sortOrder` = '". (int)$data['sortOrder'] ."'
                WHERE `_id` = '" . (int)$data['_id'] . "'");

            $json['success'][] = 'Данные элемента обновлены';
            $json['saved'] = true;

        }

        return $json;
    }

    public function flipItem($_id)
    {
        $status = $this->getPriceListStatus($_id);
        $this->updatePriceListStatus($_id, !$status);
    }

    private function getPriceListStatus($_id)
    {
        $q = $this->db->query("SELECT `status`
            FROM `". DB_PREFIX . self::PL_TABLE ."`
            WHERE `_id` = '" . (int)$_id . "'");

        if (isset($q->row['status'])) {
            return (bool)$q->row['status'];
        }
    }

    private function updatePriceListStatus($_id, $status)
    {
        $this->db->query("UPDATE `". DB_PREFIX . self::PL_TABLE ."`
            SET `status` = '". (int)$status ."'
            WHERE `_id` = '" . (int)$_id . "'");
    }

    public function getInfoPages()
    {
        $this->load->model('catalog/information');

        return array_map(function($v) {
            return array(
                'information_id' => $v['information_id'],
                'title' => $v['title'],
            );
        },
        $this->model_catalog_information->getInformations());
    }
}