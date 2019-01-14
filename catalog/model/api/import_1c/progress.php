<?php
class ModelApiImport1CProgress extends Model
{
    const PROGRESS_TABLE = 'import_1c_progress';
    const ACTION_TABLE = 'import_1c_action';
    const LOG_TABLE = 'import_1c_log';

    const SUCCESS_MESSAGE = 'success';
    const ERROR_MESSAGE = 'error';
    const INFO_MESSAGE = 'info';

    public function isProgress($api_token)
    {
        $query = $this->db->query("SELECT `progress_id`
            FROM `". DB_PREFIX . self::PROGRESS_TABLE ."`
            WHERE `api_token` = '". $this->db->escape($api_token) ."'");

        if ($query->num_rows) {
            return $query->row['progress_id'];
        } else {
            return false;
        }
    }

    public function initProgress($api_token, $data)
    {
        $this->db->query("INSERT INTO `". DB_PREFIX . self::PROGRESS_TABLE ."`
            SET `api_token` = '" . $this->db->escape($api_token) . "',
                `progress` = '" . (float)0 . "',
                `extra` = '" . $this->db->escape(json_encode($data['extra'])) . "',
                `create_date` = NOW()");

        return $this->db->getLastId();
    }

    public function saveAction($progress_id, $data)
    {
        $this->db->query("INSERT INTO `". DB_PREFIX . self::ACTION_TABLE ."`
            SET `progress_id` = '" . (int)$progress_id . "',
                `type` = '" . $this->db->escape($data['type']) . "',
                `mode` = '" . $this->db->escape($data['mode']) . "',
                `filename` = '" . $this->db->escape($data['filename']) . "',
                `create_date` = NOW()");

        return $this->db->getLastId();
    }

    public function updateProgress($progress_id, $progress)
    {
        $this->db->query("UPDATE `". DB_PREFIX . self::PROGRESS_TABLE ."`
            SET `progress` = '". (float)$progress ."',
                `update_date` = NOW()
            WHERE `progress_id` = '". (int)$progress_id ."'");
    }

    public function getExtra($progress_id)
    {
        $query = $this->db->query("SELECT `extra`
            FROM `". DB_PREFIX . self::PROGRESS_TABLE ."`
            WHERE `progress_id` = '". $this->db->escape($progress_id) ."'");

        if ($query->num_rows) {
            return json_decode($query->row['extra'], true);
        } else {
            return array();
        }
    }

    public function updateExtra($progress_id, $extra)
    {
        $this->db->query("UPDATE `". DB_PREFIX . self::PROGRESS_TABLE ."`
            SET `extra` = '". $this->db->escape(json_encode($extra)) ."',
                `update_date` = NOW()
            WHERE `progress_id` = '". (int)$progress_id ."'");
    }

    public function finishImport($progress_id)
    {
        $this->db->query("UPDATE `". DB_PREFIX . self::PROGRESS_TABLE ."`
            SET `finish_date` = NOW(),
                `update_date` = NOW(),
                `progress` = '". (float)100 ."'
            WHERE `progress_id` = '". (int)$progress_id ."'");
    }

    public function addLog($progress_id, $type, $message, $action_id = false)
    {
        $this->db->query("INSERT INTO `". DB_PREFIX . self::LOG_TABLE ."`
            SET `progress_id` = '" . (int)$progress_id . "',
                `action_id` = '" . (int)$action_id . "',
                `type` = '" . $this->db->escape($type) . "',
                `message` = '" . $this->db->escape($message) . "',
                `create_date` = NOW()");
    }

    public function parseJson($progress_id, $json, $action_id = false)
    {
        if (isset($json['success']) && is_array($json['success'])) {
            foreach ($json['success'] as $m) {
                $this->addLog($progress_id, self::SUCCESS_MESSAGE, $m, $action_id);
            }
        }

        if (isset($json['error']) && is_array($json['error'])) {
            foreach ($json['error'] as $m) {
                $this->addLog($progress_id, self::ERROR_MESSAGE, $m, $action_id);
            }
        }

        if (isset($json['message']) && is_array($json['message'])) {
            foreach ($json['message'] as $m) {
                $this->addLog($progress_id, self::INFO_MESSAGE, $m, $action_id);
            }
        }
    }
}