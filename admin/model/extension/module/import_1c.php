<?php
/*
 *  location: admin/model
 */
class ModelExtensionModuleImport1C extends Model
{
    private $codename = 'import_1c';
    private $route = 'extension/module/import_1c';

    const PROGRESS_TABLE = 'import_1c_progress';
    const ACTION_TABLE = 'import_1c_action';
    const LOG_TABLE = 'import_1c_log';

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
        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . $this->db->escape(self::PROGRESS_TABLE) ."` (
            `progress_id` int(11) NOT NULL AUTO_INCREMENT,
            `api_token` varchar(255) NOT NULL,
            `progress` decimal(15,4) NOT NULL,
            `extra` TEXT NOT NULL,
            `create_date` datetime NOT NULL,
            `update_date` datetime NOT NULL,
            `finish_date` datetime NOT NULL,
            PRIMARY KEY (`progress_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . $this->db->escape(self::ACTION_TABLE) ."` (
            `action_id` int(11) NOT NULL AUTO_INCREMENT,
            `progress_id` int(11) NOT NULL,
            `type` char(32) NOT NULL,
            `mode` char(32) NOT NULL,
            `filename` char(32) NOT NULL,
            `create_date` datetime NOT NULL,
            PRIMARY KEY (`action_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . $this->db->escape(self::LOG_TABLE) ."` (
            `log_id` int(11) NOT NULL AUTO_INCREMENT,
            `progress_id` int(11) NOT NULL,
            `action_id` int(11) NOT NULL,
            `type` char(32) NOT NULL,
            `message` TEXT NOT NULL,
            `create_date` datetime NOT NULL,
            PRIMARY KEY (`log_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");
    }

    public function dropTables()
    {
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . $this->db->escape(self::PROGRESS_TABLE) ."`");
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . $this->db->escape(self::ACTION_TABLE) ."`");
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . $this->db->escape(self::LOG_TABLE) ."`");
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