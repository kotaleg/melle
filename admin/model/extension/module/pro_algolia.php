<?php
/*
 *  location: admin/model
 */

class ModelExtensionModulePROAlgolia extends Model
{
    private $codename = 'pro_algolia';
    private $route = 'extension/module/pro_algolia';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/setting');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/db');
        $this->load->model('extension/pro_patch/url');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);
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

    public function createTables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . \pro_algolia\constant::INDEX_OBJECT_TABLE ."` (
            `_id` int(11) NOT NULL AUTO_INCREMENT,

            `objectId` varchar(255) NOT NULL,
            `objectDataHash` varchar(255) NOT NULL,
            `status` varchar(16) NOT NULL,

            `createDate` datetime NOT NULL,
            `updateDate` datetime NOT NULL,

            PRIMARY KEY (`_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . \pro_algolia\constant::QUEUE_TABLE ."` (
            `_id` int(11) NOT NULL AUTO_INCREMENT,

            `storeItemId` int(11) NOT NULL,
            `storeItemType` varchar(16) NOT NULL,

            `operation` varchar(16) NOT NULL,
            `status` varchar(16) NOT NULL,

            `createDate` datetime NOT NULL,
            `updateDate` datetime NOT NULL,

            PRIMARY KEY (`_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . \pro_algolia\constant::QUEUE_LOG_TABLE ."` (
            `queueId` int(11) NOT NULL,
            `type` varchar(16) NOT NULL,
            `message` text,
            `createDate` datetime NOT NULL
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");
    }

    public function dropTables()
    {
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . \pro_algolia\constant::INDEX_OBJECT_TABLE ."`");
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . \pro_algolia\constant::QUEUE_TABLE ."`");
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . \pro_algolia\constant::QUEUE_LOG_TABLE ."`");
    }

    private function log($message)
    {
        $this->log->write(strtoupper($this->codename)." :: {$message}");
    }

    public function getCronUrl()
    {
        return HTTPS_CATALOG . "index.php?route={$this->route}";
    }

    public function queueSaveProduct($productId)
    {
        return $this->addItemToQueue(
            \pro_algolia\constant::PRODUCT, 
            $productId, 
            \pro_algolia\constant::SAVE
        );
    }

    public function queueDeleteProduct($productId)
    {
        return $this->addItemToQueue(
            \pro_algolia\constant::PRODUCT, 
            $productId, 
            \pro_algolia\constant::DELETE
        );
    }

    private function addItemToQueue($itemType, $itemId, $operationType)
    {
        if ($this->isItemInQueue($itemType, $itemId, $operationType)) {
            return;
        }

        $this->db->query("INSERT INTO `". DB_PREFIX . pro_algolia\constant::QUEUE_TABLE ."`
            SET `storeItemId` = '". (int) $itemId ."',
                `storeItemType` = '". $this->db->escape($itemType) ."',

                `operation` = '". $this->db->escape($operationType) ."',
                `status` = '". pro_algolia\constant::UNDEFINED ."',

                `createDate` = NOW(),
                `updateDate` = NOW()");

        return $this->db->getLastId();
    }

    private function isItemInQueue($itemType, $itemId, $operationType)
    {
        return $this->db->query("SELECT *
            FROM `". DB_PREFIX . pro_algolia\constant::QUEUE_TABLE . "`
            WHERE `storeItemId` = '". (int) $itemId ."'
            AND `storeItemType` = '". $this->db->escape($itemType) ."'
            AND `operation` = '" . $this->db->escape($operationType) . "'
            AND `status` = '" . pro_algolia\constant::UNDEFINED . "'")->row;
    }
}