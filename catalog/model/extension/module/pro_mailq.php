<?php
/*
 *  location: catalog/model
 */
class ModelExtensionModuleProMailQ extends Model
{
    private $codename = 'pro_mailq';
    private $route = 'extension/module/pro_mailq';

    public function __construct($registry)
    {
        parent::__construct($registry);
    }

    public function log($type, $message, $queueId = false, $extra = array())
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . \pro_mailq\constant::LOG_TABLE . "`
            SET `queueId` = '" . (int) $queueId . "',
                `message` = '" . $this->db->escape($message) . "',
                `extra` = '" . $this->db->escape(json_encode($extra)) . "',
                `type` = '" . $this->db->escape($type) . "',
                `date` = NOW()");
    }

    public function addToQueue($data)
    {
        $check = true;
        $required = array('from', 'to', 'sender', 'subject');

        foreach ($required as $r) {
            if (!array_key_exists($r, $data)) {
                $check = false;
            }
        }

        if (!isset($data['text']) && !isset($data['html'])) {
            $check = false;
        }

        if ($check === false) {
            $this->log(\pro_mailq\constant::ERROR, 'One of the required fields missing', false, $data);
            return;
        }

        if (is_array($data['to'])) {
            foreach ($data['to'] as $to) {
                $newData = $data;
                $newData['to'] = $to;
                $this->addToQueue($newData);
            }
            return;
        }

        $sql = "INSERT INTO `" . DB_PREFIX . \pro_mailq\constant::QUEUE_TABLE . "`
            SET `from` = '" . $this->db->escape($data['from']) . "',
                `to` = '" . $this->db->escape($data['to']) . "',
                `sender` = '" . $this->db->escape($data['sender']) . "',
                `subject` = '" . $this->db->escape($data['subject']) . "',";

        if (isset($data['replyTo'])) {
            $sql .= "`replyTo` = '" . $this->db->escape($data['replyTo']) . "',";
        }
        if (isset($data['text'])) {
            $sql .= "`text` = '" . $this->db->escape($data['text']) . "',";
        }
        if (isset($data['html'])) {
            $sql .= "`html` = '" . $this->db->escape($data['html']) . "',";
        }

        $sql .= "`status` = '" . \pro_mailq\constant::UNDEFINED . "',";
        $sql .= "`date` = NOW();";

        $this->db->query($sql);
        $queueId = $this->db->getLastId();

        if (
            isset($data['attachments'])
            && is_array($data['attachments'])
        ) {
            foreach ($data['attachments'] as $a) {
                $this->addAttachment($queueId, $a);
            }
        }
    }

    private function addAttachment($queueId, $path, $type = '')
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . \pro_mailq\constant::ATTACHMENT_TABLE . "`
            SET `queueId` = '" . (int) $queueId . "',
                `attachmentType` = '" . $this->db->escape($type) . "',
                `attachmentPath` = '" . $this->db->escape($path) . "'");
    }

    private function getAttachments($queueId)
    {
        $query = $this->db->query("SELECT *
            FROM `" . DB_PREFIX . \pro_mailq\constant::ATTACHMENT_TABLE . "`
            WHERE `queueId` = '" . (int) $queueId . "'");

        return $query->rows;
    }

    public function work()
    {
        $json = array(
            'processed' => 0,
        );

        foreach ($this->getNext() as $data) {
            try {
                $data['attachments'] = $this->getAttachments($data['_id']);
                $this->processMessage($data);
                $this->updateQueueStatus($data['_id'], \pro_mailq\constant::SUCCESS);
                $json['processed']++;
            } catch (Exception $e) {
                $this->updateQueueStatus($data['_id'], \pro_mailq\constant::ERROR);
                $this->log(\pro_mailq\constant::ERROR, 'Message can\'t be sent', $data['_id'], (string) $e);
            }
        }

        return $json;
    }

    private function processMessage($data)
    {
        $setting = array(
            'host' => $this->config->get('config_mail_smtp_hostname'),
            'port' => $this->config->get('config_mail_smtp_port'),
            'user' => $this->config->get('config_mail_smtp_username'),
            'pass' => $this->config->get('config_mail_smtp_password'),
        );

        $setting = array_filter($setting, 'trim');

        $mailq = new \pro_mailq\pro_mailq($setting);

        $mailq->send($data);
    }

    private function getNext($limit = 1)
    {
        $query = $this->db->query("SELECT *
            FROM `" . DB_PREFIX . \pro_mailq\constant::QUEUE_TABLE . "`
            WHERE `status` = '" . \pro_mailq\constant::UNDEFINED . "'
            LIMIT " . (int) $limit);

        return $query->rows;
    }

    private function updateQueueStatus($queueId, $status)
    {
        $this->db->query("UPDATE `" . DB_PREFIX . \pro_mailq\constant::QUEUE_TABLE . "`
            SET `status` = '" . $this->db->escape($status) . "'
            WHERE `_id` = '" . (int) $queueId . "'");
    }
}
