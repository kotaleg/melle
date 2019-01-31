<?php

class ModelExtensionModuleAdminMonitor extends Model {
    public function __construct($registry) {
        parent::__construct($registry);
    }

    public function logEvent($data) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "adminmonitor` SET user_id='" . (int)$data['user_id'] . "', user_name='" . $this->db->escape($data['user_name']) . "', event_type='" . $this->db->escape($data['event_type']) . "', event_group='" . $this->db->escape($data['event_group']) . "', argument_hook='" . $this->db->escape($data['argument_hook']) . "', `data`='" . $this->db->escape($data['data']) . "', `subject`='" . $this->db->escape(htmlspecialchars_decode($data['subject'])) . "', date_created = NOW()");
    }

    public function getUserNameById($user_id) {
        $query = $this->db->query("SELECT username FROM `" . DB_PREFIX . "user` WHERE user_id='" . $this->db->escape($user_id) . "'");
        return $query->num_rows ? $query->row['username'] : '';
    }
}
