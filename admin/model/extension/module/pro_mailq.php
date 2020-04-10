<?php
/*
 *  location: admin/model
 */
class ModelExtensionModulePROMailq  extends Model
{
    private $codename = 'pro_mailq';
    private $route = 'extension/module/pro_mailq';

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
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . \pro_mailq\constant::QUEUE_TABLE . "` (
            `_id` int(11) NOT NULL AUTO_INCREMENT,

            `from` varchar(255) NOT NULL,
            `to` varchar(255) NOT NULL,
            `sender` varchar(255) NOT NULL,
            `replyTo` varchar(255) NOT NULL,
            `subject` varchar(255) NOT NULL,

            `text` longtext NOT NULL,
            `html` longtext NOT NULL,

            `date` timestamp NOT NULL,
            `status` char(16) NOT NULL,

            PRIMARY KEY (`_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . \pro_mailq\constant::ATTACHMENT_TABLE . "` (
            `queueId` int(11) NOT NULL,
            `attachmentType` varchar(32) NOT NULL,
            `attachmentPath` varchar(512) NOT NULL,

            PRIMARY KEY (`queueId`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . \pro_mailq\constant::LOG_TABLE . "` (
            `_id` int(11) NOT NULL AUTO_INCREMENT,

            `queueId` int(11) NOT NULL,

            `type` char(16) NOT NULL,
            `message` text NOT NULL,
            `extra` text NOT NULL,

            `date` timestamp NOT NULL,

            PRIMARY KEY (`_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");
    }

    public function dropTables()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . \pro_mailq\constant::QUEUE_TABLE . "`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . \pro_mailq\constant::ATTACHMENT_TABLE . "`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . \pro_mailq\constant::LOG_TABLE . "`");
    }

    // QUEUE

    public function getComplexQueueItemsTotal($data = array())
    {
        $q = $this->db->query("SELECT COUNT(`_id`) as `total`
            FROM `" . DB_PREFIX . \pro_mailq\constant::QUEUE_TABLE . "`");

        return (int) $q->row['total'];
    }

    public function getComplexQueueItems($data = array())
    {
        $sql = "SELECT *
            FROM `" . DB_PREFIX . \pro_mailq\constant::QUEUE_TABLE . "`";

        $sql .= " ORDER BY `date` DESC ";

        if (isset($data['start']) || isset($data['limit'])) {

            if ($data['limit'] != -1) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = (int) $this->config->get('config_limit_admin');
                }

                $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
            }
        }

        return $this->db->query($sql)->rows;
    }

    public function prepareComplexQueueItems($data)
    {
        return array_map(function($v) {
            $v['statusLabel'] = $this->getLabelForQueueStatus($v['status']);
            return $v;
        }, $this->getComplexQueueItems($data));
    }

    private function getLabelForQueueStatus($status)
    {
        switch($status) {
            case(\pro_mailq\constant::SUCCESS):
                return 'success';
                break;

            case(\pro_mailq\constant::ERROR):
                return 'danger';
                break;

            default:
                return 'default';
        }
    }

    public function updateQueueStatus($queueId, $status)
    {
        $this->db->query("UPDATE `" . DB_PREFIX . \pro_mailq\constant::QUEUE_TABLE . "`
            SET `status` = '". $this->db->escape($status) ."'
            WHERE `_id` = '". (int) $queueId ."' ");
    }

    public function resetQueueStatus($queueId)
    {
        return $this->updateQueueStatus($queueId, \pro_mailq\constant::UNDEFINED);
    }

    // LOG

    public function getComplexLogItemsTotal($data = array())
    {
        $q = $this->db->query("SELECT COUNT(`_id`) as `total`
            FROM `" . DB_PREFIX . \pro_mailq\constant::LOG_TABLE . "`");

        return (int) $q->row['total'];
    }

    public function getComplexLogItems($data = array())
    {
        $sql = "SELECT *
            FROM `" . DB_PREFIX . \pro_mailq\constant::LOG_TABLE . "`";

        $sql .= " ORDER BY `date` DESC ";

        if (isset($data['start']) || isset($data['limit'])) {

            if ($data['limit'] != -1) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = (int) $this->config->get('config_limit_admin');
                }

                $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
            }
        }

        return $this->db->query($sql)->rows;
    }

    public function prepareComplexLogItems($data)
    {
        return array_map(function($v) {
            $v['typeLabel'] = $this->getLabelForLogType($v['type']);
            return $v;
        }, $this->getComplexLogItems($data));
    }

    private function getLabelForLogType($type)
    {
        switch($type) {
            case(\pro_mailq\constant::SUCCESS):
                return 'success';
                break;

            case(\pro_mailq\constant::ERROR):
                return 'danger';
                break;

            default:
                return 'default';
        }
    }
}