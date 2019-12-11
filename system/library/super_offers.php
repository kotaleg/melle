<?php

class super_offers
{
    const OPTION_COMMBINATION = 'so_option_combination';
    const OPTION_CONNECTION = 'so_option_connection';
    const OPTION_SETTING = 'so_column_setting';

    // COLUMNS
    const IMPORT_ID = 'import_id';
    const MODEL = 'model';
    const PRODUCT_CODE = 'product_code';
    const QUANTITY = 'quantity';
    const SUBTRACT = 'subtract';
    const PRICE = 'price';
    const SPECIAL = 'special';
    const REWARD = 'reward';
    const WEIGHT = 'weight';
    const IMAGE = 'image';
    const BARCODE = 'barcode';

    // TYPES
    const TYPE_SELECT = 'select';
    const TYPE_RADIO = 'radio';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_IMAGE = 'image';

    const NULL_VALUE = '--';

    private $codename = 'super_offers';
    private $route = 'extension/module/super_offers';

    function __construct($registry)
    {
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        $this->session = $registry->get('session');
    }

    public function getSupportedOptionTypes()
    {
        return array(
            self::TYPE_SELECT, self::TYPE_RADIO, self::TYPE_IMAGE
        );
    }

    public function getDefaultActiveColumns()
    {
        return array(
            self::IMPORT_ID     => array('name' => false, 'active' => false, 'code' => self::IMPORT_ID, 'default' => ''),
            self::MODEL         => array('name' => false, 'active' => false, 'code' => self::MODEL, 'default' => ''),
            self::PRODUCT_CODE  => array('name' => false, 'active' => false, 'code' => self::PRODUCT_CODE, 'default' => ''),
            self::QUANTITY      => array('name' => false, 'active' => true, 'code' => self::QUANTITY, 'default' => ''),
            self::SUBTRACT      => array('name' => false, 'active' => false, 'code' => self::SUBTRACT, 'default' => true),
            self::PRICE         => array('name' => false, 'active' => true, 'code' => self::PRICE, 'default' => ''),
            self::SPECIAL       => array('name' => false, 'active' => false, 'code' => self::SPECIAL, 'default' => ''),
            self::REWARD        => array('name' => false, 'active' => false, 'code' => self::REWARD, 'default' => ''),
            self::WEIGHT        => array('name' => false, 'active' => false, 'code' => self::WEIGHT, 'default' => ''),
            self::BARCODE       => array('name' => false, 'active' => true, 'code' => self::BARCODE, 'default' => ''),
            self::IMAGE         => array('name' => false, 'active' => true, 'code' => self::IMAGE, 'default' => ''),
        );
    }

    public function getNullValue()
    {
        return self::NULL_VALUE;
    }

    public function allowBuyWithZeroQuantity()
    {
        return $this->config->get('config_stock_checkout');
    }

    public function clearForProduct($product_id)
    {
        $this->db->query("DELETE FROM `". DB_PREFIX . self::OPTION_COMMBINATION ."`
            WHERE `product_id` = '". (int)$product_id ."'");

        $this->db->query("DELETE FROM `". DB_PREFIX . self::OPTION_CONNECTION ."`
            WHERE `product_id` = '". (int)$product_id ."'");

        $this->db->query("DELETE FROM `". DB_PREFIX . self::OPTION_SETTING ."`
            WHERE `product_id` = '". (int)$product_id ."'");
    }

    public function isOptionsForProduct($product_id)
    {
        $sql = "SELECT COUNT(`combination_id`)
            FROM `". DB_PREFIX . self::OPTION_COMMBINATION ."`
            WHERE `product_id` = '". (int)$product_id ."'";
        $q = $this->db->query($sql);

        return (boolean)(isset($q->row['COUNT(`combination_id`)'])
            && $q->row['COUNT(`combination_id`)'] > 0);
    }

    public function _addConnection($data)
    {
        $sql = "INSERT INTO `". DB_PREFIX . self::OPTION_CONNECTION ."`
            (`option_a`, `option_value_a`, `product_id`, `combination_id`)
            VALUES(
                '" . $this->db->escape($data['option_a']) . "',
                '" . $this->db->escape($data['option_value_a']) . "',
                '" . $this->db->escape($data['product_id']) . "',
                '" . $this->db->escape($data['combination_id']) . "');";

        $this->db->query($sql);
    }

    public function _addCombination($data)
    {
        $sql = "INSERT INTO `". DB_PREFIX . self::OPTION_COMMBINATION ."`
            (`product_id`, `quantity`, `subtract`, `price`, `price_prefix`,
                `points`, `points_prefix`, `weight`, `weight_prefix`, `model`,
                `product_code`, `special_price`, `special_price_start`,
                `special_price_end`, `image`, `barcode`, `import_id`)
            VALUES(
                '" . (int)$data['product_id'] . "',
                '" . $this->db->escape($data['quantity']) . "',
                '" . $this->db->escape($data['subtract']) . "',
                '" . $this->db->escape($data['price']) . "',
                '" . $this->db->escape($data['price_prefix']) . "',
                '" . $this->db->escape($data['points']) . "',
                '" . $this->db->escape($data['points_prefix']) . "',
                '" . $this->db->escape($data['weight']) . "',
                '" . $this->db->escape($data['weight_prefix']) . "',
                '" . $this->db->escape($data['model']) . "',
                '" . $this->db->escape($data['product_code']) . "',
                '" . $this->db->escape($data['special_price']) . "',
                '" . $this->db->escape($data['special_price_start']) . "',
                '" . $this->db->escape($data['special_price_end']) . "',
                '" . $this->db->escape($data['image']) . "',
                '" . $this->db->escape($data['barcode']) . "',
                '" . $this->db->escape($data['import_id']) . "' );";

        $this->db->query($sql);
        return $this->db->getLastId();
    }

    public function _getCombinationsForProduct($product_id)
    {
        $q = $this->db->query("SELECT * FROM `". DB_PREFIX . self::OPTION_COMMBINATION ."`
            WHERE `product_id` = '". (int)$product_id ."'");

        return $q->rows;
    }

    public function _getConnectionsForCombination($product_id, $combination_id)
    {
        $q = $this->db->query("SELECT * FROM `". DB_PREFIX . self::OPTION_CONNECTION ."`
            WHERE `product_id` = '". (int)$product_id ."'
            AND `combination_id` = '". (int)$combination_id ."'");

        return $q->rows;
    }

    public function saveCombinations($product_id, $so_combination)
    {
        // CLEAR DATA BEFORE SAVE
        if (isset($product_id)) {
            $this->clearForProduct($product_id);
        }

        foreach ($so_combination as $c) {

            $comb_data = array(
                'product_id'        => $product_id,
                'quantity'          => (isset($c['quantity'])) ? $c['quantity'] : $this->getNullValue(),
                'subtract'          => (isset($c['subtract'])) ? $c['subtract'] : false,
                'price'             => (isset($c['price'])) ? $c['price'] : '',
                'price_prefix'      => '+',
                'points'            => false,
                'points_prefix'     => '+',
                'weight'            => false,
                'weight_prefix'     => '+',
                'model'             => false,
                'product_code'      => (isset($c['product_code'])) ? $c['product_code'] : '',
                'special_price'     => false,
                'special_price_start' => false,
                'special_price_end' => false,
                'image'             => (isset($c['image'])) ? $c['image'] : '',
                'barcode'           => (isset($c['barcode'])) ? $c['barcode'] : '',
                'import_id'         => (isset($c['import_id'])) ? $c['import_id'] : '',
            );

            $combination_id = $this->_addCombination($comb_data);

            foreach ($c as $k => $v) {

                $ex = explode("__", $k);
                if (!isset($ex[1])) { continue; }

                $oid = $ex[1];
                $ovid = $v;

                $conn_data = array(
                    'option_a'          => $oid,
                    'option_value_a'    => $ovid,
                    'product_id'        => $product_id,
                    'combination_id'    => $combination_id,
                );

                $this->_addConnection($conn_data);
            }
        }
    }

    public function getLowestPrice($product_id, $check_all = false)
    {
        $price = 0;
        $sql = "SELECT MIN(`price`) as min_price
            FROM `". DB_PREFIX . self::OPTION_COMMBINATION ."`
            WHERE `product_id` = '". (int)$product_id ."'
            AND `price` > 0";

        if ($check_all === false) {
            $sql .= " AND `quantity` > 0";
        }

        $q = $this->db->query($sql);

        if (isset($q->row['min_price']) && $q->row['min_price'] != null) {
            $price = round($q->row['min_price'], 0);
        } else {
            if ($check_all) { return $price; }
            return $this->getLowestPrice($product_id, true);
        }

        return $price;
    }

    public function getMinQuantity($product_id)
    {
        $quantity = 0;

        $q = $this->db->query("SELECT MIN(`quantity`) as min_q
            FROM `". DB_PREFIX . self::OPTION_COMMBINATION ."`
            WHERE `product_id` = '". (int)$product_id ."'
            AND `quantity` > 0");

        if (isset($q->row['min_q'])) {
            $quantity = (int)$q->row['min_q'];
        }

        return $quantity;
    }

    public function getMaxQuantity($product_id)
    {
        $quantity = 0;

        $q = $this->db->query("SELECT MAX(`quantity`) as max_q
            FROM `". DB_PREFIX . self::OPTION_COMMBINATION ."`
            WHERE `product_id` = '". (int)$product_id ."'
            AND `quantity` > 0");

        if (isset($q->row['max_q'])) {
            $quantity = (int)$q->row['max_q'];
        }

        return $quantity;
    }

    public function fixProducts($products_data)
    {
        foreach ($products_data as $pd_key => $pd) {

            if (isset($pd['product_id']) && isset($pd['option'])
            && is_array($pd['option']) && $pd['option']) {

                if (!$this->isOptionsForProduct($pd['product_id'])) {
                    continue;
                }

                $active_options = $this->prepareActiveOptions($pd['product_id'], $pd['option']);
                $combination = $this->getCombinationForActiveOptions($pd['product_id'], $active_options);

                if ($combination !== null) {
                    $quantity = 0;
                    if ($combination['quantity'] != $this->getNullValue()) {
                        $quantity = $combination['quantity'];
                    }

                    $stock = true;
                    if ($quantity < $pd['quantity']) {
                        $products_data[$pd_key]['quantity'] = $quantity;
                    }
                    $products_data[$pd_key]['stock'] = $stock;

                    /* SPECIAL FIX */
                    // if (isset($pd['ds_price']) && isset($pd['ds_option_price'])) {
                    //     $ds_price = ($pd['ds_price'] + $pd['ds_option_price']);
                    //     if ($ds_price > 0) { $combination['price'] = $ds_price; }
                    // }
                    /* SPECIAL FIX */

                    $products_data[$pd_key]['max_quantity'] = $quantity;
                    $products_data[$pd_key]['price'] = $combination['price'];
                    $products_data[$pd_key]['total'] = $combination['price'] * (int)$products_data[$pd_key]['quantity'];

                    if (!empty($combination['product_code'])) {
                        $products_data[$pd_key]['name'] = $combination['product_code'];
                    }
                }
            }
        }

        return $products_data;
    }

    public function prepareActiveOptions($product_id, $product_options)
    {
        $active_options = array();
        foreach ($product_options as $ok => $ov) {

            if (!is_array($ov) && is_numeric($ov)) {
                $ov = $this->getProductOptionDataById($product_id, $ov);
                if ($ov && isset($ov['type']) && !empty($ov['type'])) {
                    ///
                } else {
                    continue;
                }
            }

            if (!in_array($ov['type'], $this->getSupportedOptionTypes())) {
                continue;
            }

            $active_options[] = array(
                'option_id'         => $ov['option_id'],
                'option_value_id'   => $ov['option_value_id'],
            );
        }

        return $active_options;
    }

    private function getProductOptionDataById($product_id, $pov_id)
    {
        $q = $this->db->query("SELECT pov.option_id, pov.option_value_id, o.type
            FROM `". DB_PREFIX ."product_option_value` pov
            LEFT JOIN `". DB_PREFIX ."option` o ON (pov.option_id = o.option_id)
            WHERE `product_id` = '". (int)$product_id ."'
            AND `product_option_value_id` = '". (int)$pov_id ."'");

        return $q->row;
    }

    public function getCombinationForActiveOptions($product_id, $active_options)
    {
        $comb_q = $this->db->query("SELECT * FROM `". DB_PREFIX . self::OPTION_COMMBINATION ."`
            WHERE `product_id` = '". (int)$product_id ."'");

        foreach ($comb_q->rows as $combination) {
            $co = $this->_getConnectionsForCombination($product_id, $combination['combination_id']);

            $check = true;
            if (!$co) { $check = false; }

            foreach ($co as $connection) {
                $connection_checker = false;
                foreach ($active_options as $ao) {
                    if ($connection['option_a'] == $ao['option_id']
                    && $connection['option_value_a'] == $ao['option_value_id']) {
                        $connection_checker = true;
                    }
                }

                if ($connection_checker === false) { $check = false; }
            }

            if ($check === true) {
                return $combination;
            }
        }

        return null;
    }
}