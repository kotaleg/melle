<?php

class ModelExtensionModuleAdminMonitor extends Model {
    private $eventGroup = "adminmonitor";

    public function dropDB() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "adminmonitor`");
    }

    public function initDB($db) {
        $db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "adminmonitor` (`adminmonitor_id` int(11) NOT NULL AUTO_INCREMENT, `user_id` int(11) NOT NULL, `user_name` varchar(255) NOT NULL, `event_type` varchar(255) NOT NULL, `event_group` varchar(255) NOT NULL, `argument_hook` varchar(255) NOT NULL, `data` mediumtext NOT NULL, `subject` mediumtext NOT NULL, `date_created` datetime NOT NULL, PRIMARY KEY (`adminmonitor_id`), KEY `filters` (`user_id`,`event_type`,`event_group` (10))) ENGINE=MyISAM DEFAULT CHARSET=utf8");
    }

    public function __construct($registry) {
        $this->initDB($registry->get('db'));
        parent::__construct($registry);
    }

    public function deleteEvent($adminmonitor_id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "adminmonitor` WHERE adminmonitor_id='" . (int)$adminmonitor_id . "'");
    }

    public function logEvent($data) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "adminmonitor` SET user_id='" . (int)$data['user_id'] . "', user_name='" . $this->db->escape($data['user_name']) . "', event_type='" . $this->db->escape($data['event_type']) . "', event_group='" . $this->db->escape($data['event_group']) . "', argument_hook='" . $this->db->escape($data['argument_hook']) . "', `data`='" . $this->db->escape($data['data']) . "', `subject`='" . $this->db->escape(htmlspecialchars_decode($data['subject'])) . "', date_created = NOW()");
    }

    public function parseFilters($filters) {
        $sql = '';

        $conditions = array();

        if (!empty($filters['user_id'])) {
            $conditions[] = "`user_id` = '" . $filters['user_id'] . "'";
        }

        if (!empty($filters['type'])) {
            $conditions[] = "`event_type` = '" . $filters['type'] . "'";
        }

        if (!empty($filters['group'])) {
            $conditions[] = "`event_group` = '" . $filters['group'] . "'";
        }

        if (!empty($filters['start'])) {
            $conditions[] = "`date_created` >= '" . $filters['start'] . "'";
        }

        if (!empty($filters['end'])) {
            $conditions[] = "`date_created` <= '" . $filters['end'] . "'";
        }

        $sql .= !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql .= " ORDER BY `date_created` DESC ";

        if (!empty($filters['page']) && !empty($filters['limit'])) {
            $sql .= 'LIMIT ' . (($filters['page'] - 1) * $filters['limit']) . ',' . $filters['limit'];
        }

        return $sql;
    }

    public function listEvents($filters) {
        $results = $this->db->query("SELECT SQL_CALC_FOUND_ROWS * FROM `" . DB_PREFIX . "adminmonitor` " . $this->parseFilters($filters));
        
        return $results->rows;
    }

    public function eventsTotal($filters) {
        $this->listEvents($filters);

        $result = $this->db->query("SELECT FOUND_ROWS() as count");

        return (int)$result->row['count'];
    }

    public function getEvents() {
        $types = $this->getEventTypes();
        $groups = $this->getEventGroups();

        $result = array();

        foreach ($groups as $group) {
            foreach ($types as $type) {
                if ($type == "delete") {
                    if ($group['group'] != "setting") {
                        $result[] = array(
                            'key' => str_replace(array('{type}', '{when}'), array($type, 'pre'), $group['key']),
                            'type' => $type,
                            'group' => $group['group'],
                            'argument_hook' => 'admin_pre_' . str_replace('.', '_', $group['group']) . '_' . $type,
                            'eval' => $group['eval']
                        );
                    }
                } else {
                    $result[] = array(
                        'key' => str_replace(array('{type}', '{when}'), array($type, 'post'), $group['key']),
                        'type' => $type,
                        'group' => $group['group'],
                        'argument_hook' => 'admin_post_' . str_replace('.', '_', $group['group']) . '_' . $type,
                        'eval' => $group['eval']
                    );
                }
            }
        }

        return $result;
    }

    public function getEventTypes() {
        $types = array(
            'add',
            'edit',
            'login',
            'logout',
            'delete',
            'approve'
        );

        return $types;
    }

    public function getEventGroups() {
        $groups = array(
            'coupon' => '
                $this->load->model("marketing/coupon");
                $arg_data = $this->model_marketing_coupon->getCoupon($arg);
                if (!empty($arg_data["code"]) && !empty($arg_data["name"])) {
                    if ($event["type"] != "delete") {
                        $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $this->url->link("marketing/coupon/edit", "coupon_id=" . $arg . "&user_token={user_token}", "SSL") . "\">" . $arg_data["name"] . " (" . $arg_data["code"] . ")</a>";
                    } else {
                        $subject = $arg_data["name"] . " (" . $arg_data["code"] . ")";
                    }
                }
            ',
            'marketing' => '
                $this->load->model("marketing/marketing");
                $arg_data = $this->model_marketing_marketing->getMarketing($arg);
                if (!empty($arg_data["name"])) {
                    if ($event["type"] != "delete") {
                        $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $this->url->link("marketing/marketing/edit", "marketing_id=" . $arg . "&user_token={user_token}", "SSL") . "\">" . $arg_data["name"] . "</a>";
                    } else {
                        $subject = $arg_data["name"];
                    }
                }
            ',
            'affiliate' => '
                $this->load->model("marketing/affiliate");
                $arg_data = $this->model_marketing_affiliate->getAffiliate($arg);
                if (!empty($arg_data["firstname"])) {
                    if ($event["type"] != "delete") {
                        $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $this->url->link("marketing/affiliate/edit", "affiliate_id=" . $arg . "&user_token={user_token}", "SSL") . "\">" . $arg_data["firstname"] . " " . $arg_data["lastname"] . " (" . $arg_data["email"] . ")</a>";
                    } else {
                        $subject = $arg_data["firstname"] . " " . $arg_data["lastname"] . " (" . $arg_data["email"] . ")";
                    }
                }
            ',
            'affiliate.transaction' => '
                $this->load->model("marketing/affiliate");
                
                if ($event["type"] != "delete") {
                    $arg_pre_data = $this->db->query("SELECT * FROM " . DB_PREFIX . "affiliate_transaction WHERE affiliate_transaction_id=\'" . (int)$arg . "\'");

                    if (!empty($arg_pre_data->row["affiliate_id"])) {
                        $arg_data = $this->model_marketing_affiliate->getAffiliate($arg_pre_data->row["affiliate_id"]);

                        if (!empty($arg_data["firstname"])) {
                            $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $this->url->link("marketing/affiliate/edit", "affiliate_id=" . (int)$arg_pre_data->row["affiliate_id"] . "&user_token={user_token}", "SSL") . "\">" . $arg_data["firstname"] . " " . $arg_data["lastname"] . " (" . $arg_data["email"] . ")</a>";
                        }
                    }
                } else {
                    $arg_pre_data = $this->db->query("SELECT * FROM " . DB_PREFIX . "affiliate_transaction WHERE order_id=\'" . (int)$arg . "\'");

                    if (!empty($arg_pre_data->row["affiliate_id"])) {
                        $arg_data = $this->model_marketing_affiliate->getAffiliate($arg_pre_data->row["affiliate_id"]);
                        
                        if (!empty($arg_data["firstname"])) {
                            $subject = $arg_data["firstname"] . " " . $arg_data["lastname"] . " (" . $arg_data["email"] . ")";
                        }
                    }
                }
            ',
            'store' => '
                $this->load->model("setting/store");
                $arg_data = $this->model_setting_store->getStore($arg);
                if (!empty($arg_data["name"])) {
                    if ($event["type"] != "delete") {
                        $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $this->url->link("setting/store/edit", "store_id=" . $arg . "&user_token={user_token}", "SSL") . "\">" . $arg_data["name"] . "</a>";
                    } else {
                        $subject = $arg_data["name"];
                    }
                }
            ',
            'setting' => '
                if (empty($this->request->get["route"]) || $this->request->get["route"] != "setting/setting") return false;

                $arg_data[\'name\'] = $this->config->get(\'config_name\') . $this->language->get(\'text_default\');
                if ($event["type"] != "delete") {
                    $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $this->url->link("setting/setting", "user_token={user_token}", "SSL") . "\">" . $arg_data[\'name\'] . "</a>";
                } else {
                    $subject = $arg_data[\'name\'];
                }
            ',
            'download' => '
                $this->load->model("catalog/download");
                $arg_data = $this->model_catalog_download->getDownloadDescriptions($arg);
                if (!empty($arg_data[$this->config->get("config_language_id")]["name"])) {
                    if ($event["type"] != "delete") {
                        $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $this->url->link("catalog/download/edit", "download_id=" . $arg . "&user_token={user_token}", "SSL") . "\">" . $arg_data[$this->config->get("config_language_id")]["name"] . "</a>";
                    } else {
                        $subject = $arg_data[$this->config->get("config_language_id")]["name"];
                    }
                }
            ',
            'recurring' => '
                $this->load->model("catalog/recurring");
                $arg_data = $this->model_catalog_recurring->getRecurringDescription($arg);
                if (!empty($arg_data[$this->config->get("config_language_id")]["name"])) {
                    if ($event["type"] != "delete") {
                        $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $this->url->link("catalog/recurring/edit", "recurring_id=" . $arg . "&user_token={user_token}", "SSL") . "\">" . $arg_data[$this->config->get("config_language_id")]["name"] . "</a>";
                    } else {
                        $subject = $arg_data[$this->config->get("config_language_id")]["name"];
                    }
                }
            ',
            'filter' => '
                $this->load->model("catalog/filter");
                $arg_data = $this->model_catalog_filter->getFilterGroup($arg);
                if (!empty($arg_data["name"])) {
                    if ($event["type"] != "delete") {
                        $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $this->url->link("catalog/filter/edit", "filter_group_id=" . $arg . "&user_token={user_token}", "SSL") . "\">" . $arg_data["name"] . "</a>";
                    } else {
                        $subject = $arg_data["name"];
                    }
                }
            ',
            'attribute' => '
                $this->load->model("catalog/attribute");
                $arg_data = $this->model_catalog_attribute->getAttribute($arg);
                if (!empty($arg_data["name"])) {
                    if ($event["type"] != "delete") {
                        $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $this->url->link("catalog/attribute/edit", "attribute_id=" . $arg . "&user_token={user_token}", "SSL") . "\">" . $arg_data["name"] . "</a>";
                    } else {
                        $subject = $arg_data["name"];
                    }
                }
            ',
            'manufacturer' => '
                $this->load->model("catalog/manufacturer");
                $arg_data = $this->model_catalog_manufacturer->getManufacturer($arg);
                if (!empty($arg_data["name"])) {
                    if ($event["type"] != "delete") {
                        $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $this->url->link("catalog/manufacturer/edit", "manufacturer_id=" . $arg . "&user_token={user_token}", "SSL") . "\">" . $arg_data["name"] . "</a>";
                    } else {
                        $subject = $arg_data["name"];
                    }
                }
            ',
            'option' => '
                $this->load->model("catalog/option");
                $arg_data = $this->model_catalog_option->getOption($arg);
                if (!empty($arg_data["name"])) {
                    if ($event["type"] != "delete") {
                        $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $this->url->link("catalog/option/edit", "option_id=" . $arg . "&user_token={user_token}", "SSL") . "\">" . $arg_data["name"] . "</a>";
                    } else {
                        $subject = $arg_data["name"];
                    }
                }
            ',
            'attribute.group' => '
                $this->load->model("catalog/attribute_group");
                $arg_data = $this->model_catalog_attribute_group->getAttributeGroupDescriptions($arg);
                if (!empty($arg_data[$this->config->get("config_language_id")]["name"])) {
                    if ($event["type"] != "delete") {
                        $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $this->url->link("catalog/attribute_group/edit", "attribute_group_id=" . $arg . "&user_token={user_token}", "SSL") . "\">" . $arg_data[$this->config->get("config_language_id")]["name"] . "</a>";
                    } else {
                        $subject = $arg_data[$this->config->get("config_language_id")]["name"];
                    }
                }
            ',
            'information' => '
                $this->load->model("catalog/information");
                $arg_data = $this->model_catalog_information->getInformationDescriptions($arg);
                if (!empty($arg_data[$this->config->get("config_language_id")]["title"])) {
                    if ($event["type"] != "delete") {
                        $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $this->url->link("catalog/information/edit", "information_id=" . $arg . "&user_token={user_token}", "SSL") . "\">" . $arg_data[$this->config->get("config_language_id")]["title"] . "</a>";
                    } else {
                        $subject = $arg_data[$this->config->get("config_language_id")]["title"];
                    }
                }
            ',
            'category' => '
                $this->load->model("catalog/category");
                $arg_data = $this->model_catalog_category->getCategoryDescriptions($arg);
                if (!empty($arg_data[$this->config->get("config_language_id")]["name"])) {
                    if ($event["type"] != "delete") {
                        $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $this->url->link("catalog/category/edit", "category_id=" . $arg . "&user_token={user_token}", "SSL") . "\">" . $arg_data[$this->config->get("config_language_id")]["name"] . "</a>";
                    } else {
                        $subject = $arg_data[$this->config->get("config_language_id")]["name"];
                    }
                }
            ',
            'product' => '
                $this->load->model("catalog/product");
                $arg_data = $this->model_catalog_product->getProductDescriptions($arg);

                if (!empty($arg_data[$this->config->get("config_language_id")]["name"])) {
                    if ($event["type"] != "delete") {
                        $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $this->url->link("catalog/product/edit", "product_id=" . $arg . "&user_token={user_token}", "SSL") . "\">" . $arg_data[$this->config->get("config_language_id")]["name"] . "</a>";
                    } else {
                        $subject = $arg_data[$this->config->get("config_language_id")]["name"];
                    }
                }
            ',
            'review' => '
                $this->load->model("catalog/review");
                $arg_data = $this->model_catalog_review->getReview($arg);
                if (!empty($arg_data["product"])) {
                    if ($event["type"] != "delete") {
                        $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $this->url->link("catalog/review/edit", "review_id=" . $arg . "&user_token={user_token}", "SSL") . "\">" . $arg_data["product"] . "</a>";
                    } else {
                        $subject = $arg_data["product"];
                    }
                }
            ',
            'layout' => '
                $this->load->model("design/layout");
                $arg_data = $this->model_design_layout->getLayout($arg);
                if (!empty($arg_data["name"])) {
                    if ($event["type"] != "delete") {
                        $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $this->url->link("design/layout/edit", "layout_id=" . $arg . "&user_token={user_token}", "SSL") . "\">" . $arg_data["name"] . "</a>";
                    } else {
                        $subject = $arg_data["name"];
                    }
                }
            ',
            'customer' => '
                $this->load->model("customer/customer");
                $arg_data = $this->model_customer_customer->getCustomer($arg);

                if (!empty($arg_data["firstname"])) {
                    $name = $arg_data["firstname"] . " " . $arg_data["lastname"];
                    if ($event["type"] != "delete") {
                        $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $this->url->link("customer/customer/edit", "customer_id=" . $arg . "&user_token={user_token}", "SSL") . "\">" . $name . "</a>";
                    } else {
                        $subject = $name;
                    }
                }
            ',
            'customer_group' => '
                $this->load->model("customer/customer_group");
                $arg_data = $this->model_customer_customer_group->getCustomerGroup($arg);

                if (!empty($arg_data["name"])) {
                    $name = $arg_data["name"];
                    if ($event["type"] != "delete") {
                        $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $this->url->link("customer/customer_group/edit", "customer_group_id=" . $arg . "&user_token={user_token}", "SSL") . "\">" . $name . "</a>";
                    } else {
                        $subject = $name;
                    }
                }
            ',
            'banner' => '
                $this->load->model("design/banner");
                $arg_data = $this->model_design_banner->getBanner($arg);
                if (!empty($arg_data["name"])) {
                    if ($event["type"] != "delete") {
                        $subject = "<a data-id=\"adminmonitor_link\" href=\"" . $this->url->link("design/banner/edit", "banner_id=" . $arg . "&user_token={user_token}", "SSL") . "\">" . $arg_data["name"] . "</a>";
                    } else {
                        $subject = $arg_data["name"];
                    }
                }
            '
        );

        $result = array();

        foreach ($groups as $group => $eval) {
            $result[] = array(
                'key' => '{when}.admin.' . $group . '.{type}',
                'group' => $group,
                'eval' => $eval
            );
        }

        return $result;
    }

    public function sortTypeNameAlpha($a, $b) {
        return strcmp($a['type_name'], $b['type_name']);
    }

    public function sortGroupNameAlpha($a, $b) {
        return strcmp($a['group_name'], $b['group_name']);
    }

    public function getCustomData($group, $get) {
        if ($group == 'order') {
            return $get['order_id'];
        }
    }

    public function getCustomSubject($group, $get) {
        if ($group == 'order') {
            return "<a data-id=\"adminmonitor_link\" href=\"" . $this->url->link("sale/order/edit", "order_id=" . $get['order_id'] . "&user_token={user_token}", "SSL") . "\">" . $get['order_id'] . "</a>";
        }
    }

    public function hookEvents() {
        $this->load->model('setting/event');
        $this->model_setting_event->addEvent($this->eventGroup, "admin/model/*/before", $this->config->get('adminmonitor_module_path') . "/event_sink_before");
        $this->model_setting_event->addEvent($this->eventGroup, "admin/model/*/after", $this->config->get('adminmonitor_module_path') . "/event_sink_after");

        // Admin login related events
        $this->model_setting_event->addEvent($this->eventGroup, "admin/controller/*/before", $this->config->get('adminmonitor_module_path') . "/loginCatch");
        $this->model_setting_event->addEvent($this->eventGroup, "admin/controller/common/login/before", $this->config->get('adminmonitor_module_path') . "/loginSetup");
        $this->model_setting_event->addEvent($this->eventGroup, "admin/controller/common/logout/before", $this->config->get('adminmonitor_module_path') . "/logoutCatch");

        // ProductManager related events - we cannot hook to an after events here, because ProductManager uses exit(), so our handlers will not be called
        $this->model_setting_event->addEvent($this->eventGroup, "admin/controller/extension/module/productmanager/updateproduct/before", $this->config->get('adminmonitor_module_path') . "/productManagerHandler");
        $this->model_setting_event->addEvent($this->eventGroup, "admin/controller/extension/module/productmanager/updateproductbulk/before", $this->config->get('adminmonitor_module_path') . "/productManagerHandler");

        // Order editing related events
        $this->model_setting_event->addEvent($this->eventGroup, "admin/controller/sale/order/add/before", $this->config->get('adminmonitor_module_path') . "/setUserIdCookie");
        $this->model_setting_event->addEvent($this->eventGroup, "admin/controller/sale/order/info/before", $this->config->get('adminmonitor_module_path') . "/setUserIdCookie");
        $this->model_setting_event->addEvent($this->eventGroup, "admin/controller/sale/order/edit/before", $this->config->get('adminmonitor_module_path') . "/setUserIdCookie");
        $this->model_setting_event->addEvent($this->eventGroup, "catalog/model/checkout/order/addOrder/after", $this->config->get('adminmonitor_module_path') . "/logOrderAdd");
        $this->model_setting_event->addEvent($this->eventGroup, "catalog/model/checkout/order/editOrder/after", $this->config->get('adminmonitor_module_path') . "/logOrderEdit");
        $this->model_setting_event->addEvent($this->eventGroup, "catalog/model/checkout/order/addOrderHistory/after", $this->config->get('adminmonitor_module_path') . "/logOrderEdit");
        $this->model_setting_event->addEvent($this->eventGroup, "catalog/model/checkout/order/deleteOrder/after", $this->config->get('adminmonitor_module_path') . "/logOrderDelete");
    }

    public function unhookEvents() {
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode($this->eventGroup);
    }
}
