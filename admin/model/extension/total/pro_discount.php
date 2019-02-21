<?php
/*
 *  location: admin/model
 */
class ModelExtensionTotalProDiscount extends Model
{
    private $codename = 'pro_discount';
    private $route = 'extension/total/pro_discount';

    const DISCOUNT_TABLE = 'pd_discounts';
    const CATEGORY_TABLE = 'pd_category';
    const PRODUCT_TABLE = 'pd_product';
    const MANUFACTURER_TABLE = 'pd_manufacturer';
    const CUSTOMER_TABLE = 'pd_customer';

    const SALE = 'sale';
    const SALE_COUNT = 'sale_count';

    const MONEY = 'money';
    const PERCENT = 'percent';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/setting');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/db');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);
        $this->pro_discount = new \pro_discount($registry);
    }

    public function createTables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . self::DISCOUNT_TABLE ."` (
            `discount_id` int(11) NOT NULL AUTO_INCREMENT,

            `type` char(32) NOT NULL,
            `sort_order` int(11) NOT NULL,

            `start_sum` int(11) NOT NULL,
            `start_count` int(11) NOT NULL,
            `sum_and_count` tinyint(1) NOT NULL,
            `registered_only` tinyint(1) NOT NULL,

            `value` int(11) NOT NULL,
            `sign` char(32) NOT NULL,

            `products_count` int(11) NOT NULL,
            `count_like` int(11) NOT NULL,

            `status` tinyint(1),
            `name` varchar(255),
            `description` TEXT NOT NULL,

            `start_date` datetime NOT NULL,
            `finish_date` datetime NOT NULL,

            PRIMARY KEY (`discount_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . self::CATEGORY_TABLE ."` (
            `discount_id` int(11) NOT NULL,
            `category_id` int(11) NOT NULL,
            PRIMARY KEY (`discount_id`,`category_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . self::MANUFACTURER_TABLE ."` (
            `discount_id` int(11) NOT NULL,
            `manufacturer_id` int(11) NOT NULL,
            PRIMARY KEY (`discount_id`,`manufacturer_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . self::PRODUCT_TABLE ."` (
            `discount_id` int(11) NOT NULL,
            `product_id` int(11) NOT NULL,
            PRIMARY KEY (`discount_id`,`product_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . self::CUSTOMER_TABLE ."` (
            `discount_id` int(11) NOT NULL,
            `customer_id` int(11) NOT NULL,
            PRIMARY KEY (`discount_id`,`customer_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");
    }

    public function dropTables()
    {
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . self::DISCOUNT_TABLE ."`");
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . self::CATEGORY_TABLE ."`");
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . self::MANUFACTURER_TABLE ."`");
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . self::CUSTOMER_TABLE ."`");
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . self::PRODUCT_TABLE ."`");
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

    public function getDiscounts()
    {
        $discounts = array();

        $q = $this->db->query("SELECT `discount_id`
            FROM `". DB_PREFIX . self::DISCOUNT_TABLE ."`");

        foreach ($q->rows as $item) {
            $d = $this->getDiscount($item['discount_id']);
            if ($d) { $discounts[] = $d; }
        }

        return $discounts;
    }

    public function getDiscount($discount_id, $full = false)
    {
        $discount = array(
            'discount_id' => '',
            'type' => self::SALE,
            'sort_order' => 0,
            'status' => true,
            'name' => '',
            'description' => '',

            'start_sum' => 0,
            'start_count' => 0,
            'sum_and_count' => true,
            'registered_only' => false,
            'value' => 0,
            'sign' => self::PERCENT,

            'products_count' => 0,
            'count_like' => 0,

            'start_date' => '',
            'finish_date' => '',
        );

        if ($discount_id) {
            $q = $this->db->query("SELECT *
                FROM `". DB_PREFIX . self::DISCOUNT_TABLE ."`
                WHERE `discount_id` = '" . (int)$discount_id . "'");

            if (isset($q->row['discount_id'])) {
                $discount['discount_id'] = (int)$q->row['discount_id'];

                $discount['type'] = (string)$q->row['type'];
                $discount['type_name'] = $discount['type'];

                foreach ($this->getAllTypes() as $t) {
                    if ($t['id'] === $discount['type_name']) {
                        $discount['type_name'] = $t['name'];
                    }
                }

                $discount['sort_order'] = (int)$q->row['sort_order'];
                $discount['status'] = (bool)$q->row['status'];
                $discount['name'] = (string)$q->row['name'];
                $discount['description'] = (string)$q->row['description'];

                $discount['start_sum'] = (int)$q->row['start_sum'];
                $discount['start_count'] = (int)$q->row['start_count'];
                $discount['sum_and_count'] = (bool)$q->row['sum_and_count'];
                $discount['registered_only'] = (bool)$q->row['registered_only'];
                $discount['value'] = (int)$q->row['value'];
                $discount['sign'] = (string)$q->row['sign'];

                $discount['products_count'] = (int)$q->row['products_count'];
                $discount['count_like'] = (int)$q->row['count_like'];

                $start_date = $q->row['start_date'];
                if (!empty($start_date)) {
                    $start_date = date("Y-m-d", strtotime($start_date));
                }

                $finish_date = $q->row['finish_date'];
                if (!empty($finish_date)) {
                    $finish_date = date("Y-m-d", strtotime($finish_date));
                }

                $discount['start_date'] = $start_date;
                $discount['finish_date'] = $finish_date;
            }
        }

        if ($full === true) {
            $discount['categories'] = array();
            $discount['products'] = array();
            $discount['manufacturers'] = array();
            $discount['customers'] = array();

            if ($discount_id) {
                $extra = $this->pro_discount->getDiscountExtra($discount_id);
                $discount['categories'] = $extra['categories'];
                $discount['products'] = $extra['products'];
                $discount['manufacturers'] = $extra['manufacturers'];
                $discount['customers'] = $extra['customers'];
            }
        }

        return $discount;
    }

    public function saveDiscount($data)
    {
        $json['saved'] = false;

        if ($data['type'] != self::SALE && $data['type'] != self::SALE_COUNT) {
            $json['error'][] = 'Неверный тип скидки';
        }

        if (empty($data['start_date'])) {
            $json['error'][] = 'Укажите дату начала';
        }

        if (empty($data['finish_date'])) {
            $json['error'][] = 'Укажите дату окончания';
        }

        if ((utf8_strlen($data['name']) < 1) || (utf8_strlen($data['name']) > 32)) {
            $json['error'][] = 'Какое то хреновое имя';
        }

        if (!isset($json['error']) && $data['type'] === self::SALE
        && ($data['sign'] !== self::MONEY && $data['sign'] !== self::PERCENT)) {
            $json['error'][] = 'Неверная единица измерения скидки';
        }

        if (!isset($json['error'])) {

            if (!$data['discount_id']) {
                $this->db->query("INSERT INTO `". DB_PREFIX . self::DISCOUNT_TABLE ."`
                    (`type`, `sort_order`, `start_sum`, `start_count`, `sum_and_count`,
                    `registered_only`, `value`, `sign`, `products_count`, `count_like`,
                    `status`, `name`, `description`, `start_date`, `finish_date`)
                    VALUES (
                        '". $this->db->escape($data['type']) ."',
                        '". (int)$data['sort_order'] ."',
                        '". (int)$data['start_sum'] ."',
                        '". (int)$data['start_count'] ."',
                        '". (bool)$data['sum_and_count'] ."',
                        '". (bool)$data['registered_only'] ."',
                        '". (int)$data['value'] ."',
                        '". $this->db->escape($data['sign']) ."',
                        '". (int)$data['products_count'] ."',
                        '". (int)$data['count_like'] ."',
                        '". (bool)$data['status'] ."',
                        '". $this->db->escape($data['name']) ."',
                        '". $this->db->escape($data['description']) ."',
                        '". $this->db->escape( date("Y-m-d H:i:s", strtotime($data['start_date'])) ) ."',
                        '". $this->db->escape( date("Y-m-d H:i:s", strtotime($data['finish_date'])) ) ."'
                    )");

                $data['discount_id'] = $this->db->getLastId();

                $json['success'][] = 'Скидка создана';
            } else {
                $this->db->query("UPDATE `". DB_PREFIX . self::DISCOUNT_TABLE ."`
                    SET `type` = '". $this->db->escape($data['type']) ."',
                        `sort_order` = '". (int)$data['sort_order'] ."',
                        `start_sum` = '". (int)$data['start_sum'] ."',
                        `start_count` = '". (int)$data['start_count'] ."',
                        `sum_and_count` = '". (int)$data['sum_and_count'] ."',
                        `registered_only` = '". (int)$data['registered_only'] ."',
                        `value` = '". (int)$data['value'] ."',
                        `sign` = '". $this->db->escape($data['sign']) ."',
                        `products_count` = '". (int)$data['products_count'] ."',
                        `count_like` = '". (int)$data['count_like'] ."',
                        `status` = '". (int)$data['status'] ."',
                        `name` = '". $this->db->escape($data['name']) ."',
                        `description` = '". $this->db->escape($data['description']) ."',
                        `start_date` = '". $this->db->escape( date("Y-m-d H:i:s", strtotime($data['start_date'])) ) ."',
                        `finish_date` = '". $this->db->escape( date("Y-m-d H:i:s", strtotime($data['finish_date'])) ) ."'
                    WHERE `discount_id` = '" . (int)$data['discount_id'] . "'");

                $json['success'][] = 'Данные скидки обновлены';
            }

            $this->db->query("DELETE FROM `". DB_PREFIX . self::CATEGORY_TABLE ."`
                WHERE `discount_id` = '" . (int)$data['discount_id'] . "'");
            $this->db->query("DELETE FROM `". DB_PREFIX . self::MANUFACTURER_TABLE ."`
                WHERE `discount_id` = '" . (int)$data['discount_id'] . "'");
            $this->db->query("DELETE FROM `". DB_PREFIX . self::PRODUCT_TABLE ."`
                WHERE `discount_id` = '" . (int)$data['discount_id'] . "'");
            $this->db->query("DELETE FROM `". DB_PREFIX . self::CUSTOMER_TABLE ."`
                WHERE `discount_id` = '" . (int)$data['discount_id'] . "'");

            foreach ($data['categories'] as $id) {
                $this->db->query("INSERT INTO `". DB_PREFIX . self::CATEGORY_TABLE ."`
                    SET discount_id = '" . (int)$data['discount_id'] . "',
                        category_id = '" . (int)$id . "'");
            }
            foreach ($data['manufacturers'] as $id) {
                $this->db->query("INSERT INTO `". DB_PREFIX . self::MANUFACTURER_TABLE ."`
                    SET discount_id = '" . (int)$data['discount_id'] . "',
                        manufacturer_id = '" . (int)$id . "'");
            }
            foreach ($data['products'] as $id) {
                $this->db->query("INSERT INTO `". DB_PREFIX . self::PRODUCT_TABLE ."`
                    SET discount_id = '" . (int)$data['discount_id'] . "',
                        product_id = '" . (int)$id . "'");
            }
            foreach ($data['customers'] as $id) {
                $this->db->query("INSERT INTO `". DB_PREFIX . self::CUSTOMER_TABLE ."`
                    SET discount_id = '" . (int)$data['discount_id'] . "',
                        customer_id = '" . (int)$id . "'");
            }

            $json['saved'] = true;
        }

        return $json;
    }

    public function removeDiscount($discount_id)
    {
        $this->db->query("DELETE FROM `". DB_PREFIX . self::DISCOUNT_TABLE ."`
            WHERE `discount_id` = '" . (int)$discount_id . "'");
        $this->db->query("DELETE FROM `". DB_PREFIX . self::CATEGORY_TABLE ."`
            WHERE `discount_id` = '" . (int)$discount_id . "'");
        $this->db->query("DELETE FROM `". DB_PREFIX . self::MANUFACTURER_TABLE ."`
            WHERE `discount_id` = '" . (int)$discount_id . "'");
        $this->db->query("DELETE FROM `". DB_PREFIX . self::PRODUCT_TABLE ."`
            WHERE `discount_id` = '" . (int)$discount_id . "'");
        $this->db->query("DELETE FROM `". DB_PREFIX . self::CUSTOMER_TABLE ."`
            WHERE `discount_id` = '" . (int)$discount_id . "'");
    }

    public function flipDiscountStatus($discount_id)
    {
        $q = $this->db->query("SELECT `status`
            FROM `". DB_PREFIX . self::DISCOUNT_TABLE ."`
            WHERE `discount_id` = '" . (int)$discount_id . "'");

        if ($q->row && isset($q->row['status'])) {
            $this->db->query("UPDATE `". DB_PREFIX . self::DISCOUNT_TABLE ."`
                SET `status` = '" . (bool)!$q->row['status'] . "'
                WHERE `discount_id` = '" . (int)$discount_id . "'");
        }
    }

    /* CATEGORIES */

    public function getAllCategories($query = null, $discount_id = null)
    {
        $sql = "SELECT c.category_id AS id, cd.name
            FROM `". DB_PREFIX . "category` c
            LEFT JOIN `". DB_PREFIX ."category_description` cd
            ON(c.category_id = cd.category_id)";

        $glue = 'WHERE';

        if ($discount_id !== null) {
            $sql .= " LEFT JOIN `". DB_PREFIX . self::CATEGORY_TABLE ."` dd ";
            $sql .= " ON(dd.category_id = c.category_id) ";
            $sql .= " WHERE dd.discount_id = '" . (int)$discount_id . "' ";
            $glue = 'AND';
        }

        $sql .= " {$glue} cd.language_id = '" . (int)$this->config->get('config_language_id') . "' ";

        if ($query !== null) {
            $sql .= " AND cd.name LIKE '%" . $this->db->escape($query) . "%' ";
        }

        $sql .= " LIMIT 20";

        return $this->db->query($sql)->rows;
    }

    /* MANUFACTURERS */

    public function getAllManufacturers($query = null, $discount_id = null)
    {
        $sql = "SELECT m.manufacturer_id AS id, m.name
            FROM `". DB_PREFIX . "manufacturer` m";

        $glue = 'WHERE';

        if ($discount_id !== null) {
            $sql .= " LEFT JOIN `". DB_PREFIX . self::MANUFACTURER_TABLE ."` dd ";
            $sql .= " ON(dd.manufacturer_id = m.manufacturer_id) ";
            $sql .= " WHERE dd.discount_id = '" . (int)$discount_id . "' ";
            $glue = 'AND';
        }

        if ($query !== null) {
            $sql .= " {$glue} m.name LIKE '%" . $this->db->escape($query) . "%' ";
        }

        $sql .= " LIMIT 20";

        return $this->db->query($sql)->rows;
    }

    /* CUSTOMERS */

    public function getAllCustomers($query = null, $discount_id = null)
    {
        $sql = "SELECT c.customer_id AS id, c.email AS name
            FROM `". DB_PREFIX ."customer` c";

        $glue = 'WHERE';

        if ($discount_id !== null) {
            $sql .= " LEFT JOIN `". DB_PREFIX . self::CUSTOMER_TABLE ."` dd ";
            $sql .= " ON(dd.customer_id = c.customer_id) ";
            $sql .= " WHERE dd.discount_id = '" . (int)$discount_id . "' ";
            $glue = 'AND';
        }

        if ($query !== null) {
            $sql .= " {$glue} c.email LIKE '%" . $this->db->escape($query) . "%' ";
        }

        $sql .= " LIMIT 20";

        return $this->db->query($sql)->rows;
    }

    /* PRODUCTS */

    public function getAllProducts($query = null, $discount_id = null)
    {
        $sql = "SELECT p.product_id AS id, pd.h1, pd.name
            FROM `". DB_PREFIX . "product` p
            LEFT JOIN `". DB_PREFIX ."product_description` pd
            ON(p.product_id = pd.product_id)";

        $glue = 'WHERE';

        if ($discount_id !== null) {
            $sql .= " LEFT JOIN `". DB_PREFIX . self::PRODUCT_TABLE ."` dd ";
            $sql .= " ON(dd.product_id = p.product_id) ";
            $sql .= " WHERE dd.discount_id = '" . (int)$discount_id . "' ";
            $glue = 'AND';
        }

        $sql .= " {$glue} pd.language_id = '" . (int)$this->config->get('config_language_id') . "' ";

        if ($query !== null) {
            $sql .= " AND pd.name LIKE '%" . $this->db->escape($query) . "%' ";
            $sql .= " OR pd.h1 LIKE '%" . $this->db->escape($query) . "%' ";
        }

        $sql .= " LIMIT 20";

        return $this->db->query($sql)->rows;
    }

    /* TYPES */

    public function getAllTypes()
    {
        return array(
            array(
                'id' => self::SALE,
                'name' => 'Скидка',
            ),
            array(
                'id' => self::SALE_COUNT,
                'name' => '2 по цене 1',
            ),
        );
    }

    /* SIGNS */

    public function getAllSigns()
    {
        return array(
            array(
                'id' => self::MONEY,
                'name' => 'Деньги',
            ),
            array(
                'id' => self::PERCENT,
                'name' => 'Проценты',
            ),
        );
    }

    public function prepareForTree($data)
    {
        $result = array();

        if (is_array($data)) {
            foreach ($data as $item) {

                $name = $item['name'];
                if (isset($item['h1'])
                && !empty($item['h1'])) {
                    $name = $item['h1'];
                }

                $result[] = array(
                    'id'    => $item['id'],
                    'label' => $name,
                );
            }
        }

        return $result;
    }
}