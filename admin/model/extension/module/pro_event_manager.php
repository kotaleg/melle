<?php
/*
 *  location: admin/model
 */
class ModelExtensionModuleProEventManager extends Model
{
    public function addEvent($code, $trigger, $action, $status = 1, $sortOrder = 0)
    {
        if (VERSION >= '2.3.0.0' && VERSION < '3.0.0.0') {
            // TODO: fix database conflicts
        }

        $this->db->query("INSERT INTO `". DB_PREFIX ."event`
            SET `code` = '". $this->db->escape($code) ."',
                `trigger` = '". $this->db->escape($trigger) ."',
                `action` = '". $this->db->escape($action) ."',
                `status` = '". (int) $status ."',
                `sort_order` = '". (int) $sortOrder ."'");

        return $this->db->getLastId();
    }

    public function deleteEvent($code)
    {
        if (VERSION >= '3.0.0.0') {
            $this->load->model('setting/event');
            return $this->model_setting_event->deleteEventByCode($code);
        } elseif (VERSION > '2.0.0.0') {
            $this->load->model('extension/event');
            return $this->model_extension_event->deleteEvent($code);
        } else {
            return $this->deleteEventByCode($code);
        }
    }

    public function deleteEventById($event_id)
    {
        $this->db->query("DELETE FROM `". DB_PREFIX ."event`
            WHERE `event_id` = '". (int) $event_id ."'");
    }

    public function deleteEventByCode($code)
    {
        $this->db->query("DELETE FROM `". DB_PREFIX ."event`
            WHERE `code` = '". $this->db->escape($code) ."'");
    }

    public function getEventById($eventId)
    {
        $event = $this->db->query("SELECT * FROM `". DB_PREFIX ."event`
            WHERE `event_id` = '". (int) $eventId ."'")->row;
    }

    public function getEventByCode($code)
    {
        $this->db->query("SELECT DISTINCT * FROM `". DB_PREFIX ."event`
            WHERE `code` = '". $this->db->escape($code) ."'
            LIMIT 1")->row;
    }

    public function enableEvent($eventId)
    {
        if (VERSION >= '3.0.0.0') {
            $this->load->model('setting/event');
            return $this->model_setting_event->enableEvent($eventId);
        } elseif (VERSION > '2.3.0.0') {
            $this->load->model('extension/event');
            return $this->model_extension_event->enableEvent($eventId);
        } else {
            $this->updateEventStatus(true);
        }
    }

    public function disableEvent($eventId)
    {
        if (VERSION >= '3.0.0.0') {
            $this->load->model('setting/event');
            return $this->model_setting_event->disableEvent($eventId);
        } elseif(VERSION > '2.3.0.0') {
            $this->load->model('extension/event');
            return $this->model_extension_event->disableEvent($eventId);
        } else {
            $this->updateEventStatus(false);
        }
    }

    public function updateEventStatus($eventId, $status)
    {
        $this->db->query("UPDATE `". DB_PREFIX ."event`
            SET `status` = '". (bool) $status ."'
            WHERE `event_id` = '". (int) $eventId ."'");
    }
}