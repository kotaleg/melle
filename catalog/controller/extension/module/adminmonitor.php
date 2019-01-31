<?php

class ControllerExtensionModuleAdminMonitor extends Controller {
    public static $loggedOrderEvent = false;

    private $error = array();

    public function __construct($registry) {
        parent::__construct($registry);
        $this->load->config('isenselabs/adminmonitor');
        $this->load->model($this->config->get('adminmonitor_module_path'));
    }

    public function logOrderAdd($route, $data, $order_id) {
        if (self::$loggedOrderEvent) return;

        if (!empty($this->session->data['api_id']) && !empty($_COOKIE['amui']) && is_numeric($_COOKIE['amui'])) {
            $type = 'add';
            $group = 'order';
            $user_id = (int)$_COOKIE['amui'];
            $username = $this->{$this->config->get('adminmonitor_model_key')}->getUserNameById($user_id);
            $order_edit_link = str_replace("sale/order/add", "sale/order/edit&order_id=" . $order_id, $this->request->server["HTTP_REFERER"]);
            $order_edit_link = preg_replace("/user_token=\w+?(?=[&^])/", "user_token={user_token}", $order_edit_link);
            $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $order_edit_link . "\">" . $order_id . "</a>";

            $this->{$this->config->get('adminmonitor_model_key')}->logEvent(array(
                'user_id' => $user_id,
                'user_name' => $username,
                'event_type' => $type,
                'event_group' => $group,
                'argument_hook' => 'custom_' . $type,
                'data' => $order_id,
                'subject' => $subject
            ));

            self::$loggedOrderEvent = true;
        }
    }

    public function logOrderEdit($route, $data, $output) {
        if (self::$loggedOrderEvent) return;

        $order_id = (int)$data[0];

        if (!empty($this->session->data['api_id']) && !empty($_COOKIE['amui']) && is_numeric($_COOKIE['amui'])) {
            $type = 'edit';
            $group = 'order';
            $user_id = (int)$_COOKIE['amui'];
            $username = $this->{$this->config->get('adminmonitor_model_key')}->getUserNameById($user_id);
            $order_edit_link = str_replace("sale/order/info", "sale/order/edit" . $order_id, $this->request->server["HTTP_REFERER"]);
            $order_edit_link = preg_replace("/user_token=\w+?(?=[&^])/", "user_token={user_token}", $order_edit_link);
            $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $order_edit_link . "\">" . $order_id . "</a>";

            $this->{$this->config->get('adminmonitor_model_key')}->logEvent(array(
                'user_id' => $user_id,
                'user_name' => $username,
                'event_type' => $type,
                'event_group' => $group,
                'argument_hook' => 'custom_' . $type,
                'data' => $order_id,
                'subject' => $subject
            ));

            self::$loggedOrderEvent = true;
        }
    }

    public function logOrderDelete($route, $data, $output) {
        if (self::$loggedOrderEvent) return;

        $order_id = (int)$data[0];

        if (!empty($this->session->data['api_id']) && !empty($_COOKIE['amui']) && is_numeric($_COOKIE['amui'])) {
            $type = 'delete';
            $group = 'order';
            $user_id = (int)$_COOKIE['amui'];
            $username = $this->{$this->config->get('adminmonitor_model_key')}->getUserNameById($user_id);
            $subject = $order_id;

            $this->{$this->config->get('adminmonitor_model_key')}->logEvent(array(
                'user_id' => $user_id,
                'user_name' => $username,
                'event_type' => $type,
                'event_group' => $group,
                'argument_hook' => 'custom_' . $type,
                'data' => $order_id,
                'subject' => $subject
            ));

            self::$loggedOrderEvent = true;
        }
    }
}
