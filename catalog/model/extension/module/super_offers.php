<?php
/*
 *  location: admin/model
 */
class ModelExtensionModuleSuperOffers extends Model
{
    const OPTION_COMMBINATION = 'so_option_combination';
    const OPTION_CONNECTION = 'so_option_connection';
    const OPTION_SETTING = 'so_column_setting';

    const NULL_VALUE = '--';

    private $codename = 'super_offers';
    private $route = 'extension/module/super_offers';

    public function __construct($registry)
    {
        parent::__construct($registry);
    }

    public function clearForProduct($product_id)
    {
        $this->db->query("DELETE FROM `". DB_PREFIX . $this->db->escape(self::OPTION_COMMBINATION) ."`
            WHERE `product_id` = '". (int)$product_id ."'");

        $this->db->query("DELETE FROM `". DB_PREFIX . $this->db->escape(self::OPTION_CONNECTION) ."`
            WHERE `product_id` = '". (int)$product_id ."'");

        $this->db->query("DELETE FROM `". DB_PREFIX . $this->db->escape(self::OPTION_SETTING) ."`
            WHERE `product_id` = '". (int)$product_id ."'");
    }

    private function _addConnection($data)
    {
        $sql = "INSERT INTO `". DB_PREFIX . $this->db->escape(self::OPTION_CONNECTION) ."`
            (`option_a`, `option_value_a`, `product_id`, `combination_id`)
            VALUES(
                '" . $this->db->escape($data['option_a']) . "',
                '" . $this->db->escape($data['option_value_a']) . "',
                '" . $this->db->escape($data['product_id']) . "',
                '" . $this->db->escape($data['combination_id']) . "');";

        $this->db->query($sql);
    }

    private function _addCombination($data)
    {
        $sql = "INSERT INTO `". DB_PREFIX . $this->db->escape(self::OPTION_COMMBINATION) ."`
            (`product_id`, `quantity`, `subtract`, `price`, `price_prefix`,
                `points`, `points_prefix`, `weight`, `weight_prefix`, `model`,
                `product_code`, `special_price`, `special_price_start`, `special_price_end`,
                `import_id`)
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
                '" . $this->db->escape($data['import_id']) . "' );";

        $this->db->query($sql);
        return $this->db->getLastId();
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
                'quantity'          => (isset($c['quantity'])) ? $c['quantity'] : self::NULL_VALUE,
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

}