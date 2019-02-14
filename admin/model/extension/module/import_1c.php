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

    public function getRunningProgresses()
    {
        $progresses = array();
        $query = $this->db->query("SELECT DISTINCT `progress_id`
            FROM `". DB_PREFIX . $this->db->escape(self::ACTION_TABLE) ."`
            WHERE `create_date` >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ORDER BY `progress_id` DESC");

        foreach ($query->rows as $v) {
            $progresses[] = $v['progress_id'];
        }

        return $progresses;
    }

    public function getProgress($progress_id)
    {
        $query = $this->db->query("SELECT *
            FROM `". DB_PREFIX . $this->db->escape(self::PROGRESS_TABLE) ."`
            WHERE `progress_id` = '". (int)$progress_id ."'");

        if ($query->num_rows) {
            return array(
                'progress_id' => $query->row['progress_id'],
                'api_token' => $query->row['api_token'],
                'progress' => $query->row['progress'],
                'extra' => json_decode($query->row['extra'], true),
            );
        }
    }

    public function getLogs($progress_id)
    {
        $logs = array();
        $query = $this->db->query("SELECT *
            FROM `". DB_PREFIX . $this->db->escape(self::LOG_TABLE) ."`
            WHERE `progress_id` = '". (int)$progress_id ."'
            ORDER BY `log_id` DESC LIMIT 10");

        foreach ($query->rows as $l) {

            $type = $l['type'];
            if (strcmp($type, 'error') === 0) {
                $type = 'danger';
            }

            $logs[] = array(
                'type' => $type,
                'message' => $l['message'],
                'create_date' => $l['create_date'],
            );
        }

        return $logs;
    }

    public function getActions($progress_id)
    {
        $actions = array();
        $query = $this->db->query("SELECT *
            FROM `". DB_PREFIX . $this->db->escape(self::ACTION_TABLE) ."`
            WHERE `progress_id` = '". (int)$progress_id ."'
            ORDER BY `action_id` DESC LIMIT 10");

        foreach ($query->rows as $l) {
            $actions[] = array(
                'type' => $l['type'],
                'mode' => $l['mode'],
                'filename' => $l['filename'],
                'create_date' => $l['create_date'],
            );
        }

        return $actions;
    }

    public function getRunningImports()
    {
        $imports = array();

        $progresses = $this->getRunningProgresses();
        foreach ($progresses as $progress_id) {

            $progress = $this->getProgress($progress_id);
            if (!$progress) { continue; }

            $files_uploaded = 0;
            $files_processed = 0;

            if (isset($progress['extra']['files_uploaded'])) {
                $files_uploaded = (int)$progress['extra']['files_uploaded'];
            }

            if (isset($progress['extra']['files_precessed'])) {
                $files_precessed = (int)$progress['extra']['files_precessed'];
            }

            $d = array(
                'id' => $progress_id,
                'files_uploaded' => $files_uploaded,
                'files_processed' => $files_processed,
                'logs' => $this->getLogs($progress_id),
                'actions' => $this->getActions($progress_id),
            );

            $imports[] = $d;
        }

        return $imports;
    }

    public function getApiToken()
    {
        $this->load->model('user/api');

        if ($this->config->get('config_api_id')) {
            $session = new Session($this->config->get('session_engine'), $this->registry);
            $session->start();

            $this->model_user_api->addApiSession($this->config->get('config_api_id'), $session->getId(), $this->request->server['REMOTE_ADDR']);

            $session->data['api_id'] = $api_info['api_id'];

            return $session->getId();
        }
    }


    public function getColorByImportID($import_id)
    {
        $color_query = $this->db->query("SELECT * FROM `". DB_PREFIX ."color_images`
            WHERE `import_id` = '". $this->db->escape($import_id) ."'");
        if (isset($color_query->row['image']) && !empty($color_query->row['image'])) {
            return $color_query->row['image'];
        }
    }

    public function saveColorImage($import_id, $image_path)
    {
        $sql = $this->model_extension_pro_patch_db->sqlOnDuplicateUpdateBuilder(
                'color_images',
                array(
                    'import_id' => array(
                        'update'    => false,
                        'data'      => $import_id,
                    ),
                    'image' => $image_path,
                ));

        $this->db->query($sql);
    }
}