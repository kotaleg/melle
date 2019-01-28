<?php
/*
 *  location: admin/model
 */
class ModelExtensionModuleSuperOffers extends Model
{
    const OPTION_COMMBINATION = 'so_option_combination';
    const OPTION_CONNECTION = 'so_option_connection';
    const OPTION_SETTING = 'so_column_setting';

    private $codename = 'super_offers';
    private $route = 'extension/module/super_offers';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language('product/product');
        $this->load->model('catalog/product');
        $this->load->model('extension/pro_patch/url');

        if (!$this->super_offers) {
            $this->super_offers = new \super_offers($registry); }
    }

    public function isOptionsForProduct($product_id)
    {
        return $this->super_offers->isOptionsForProduct($product_id);
    }

    public function clearForProduct($product_id)
    {
        return $this->super_offers->clearForProduct($product_id);
    }

    public function saveCombinations($product_id, $so_combination)
    {
        return $this->super_offers->saveCombinations($product_id, $so_combination);
    }

    public function getOptions($product_id)
    {
        $this->load->model('tool/image');
        $po_data = array();

        foreach ($this->model_catalog_product->getProductOptions($product_id) as $po) {

            $name = mb_strtolower($po['name']);

            $po_class = '';
            switch ($name) {
                case 'размер':
                    $po_class = 'size';
                    break;

                case 'цвет':
                    $po_class = 'color';
                    break;
            }

            $po_value_data = array();

            foreach ($po['product_option_value'] as $pov) {

                $pov_image = $this->model_tool_image->resize($pov['image'], 50, 50);
                if ($po_class !== 'color') {
                    if (!is_file(DIR_IMAGE . $pov['image'])
                    || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $pov['image'])), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
                        $pov_image = false;
                    }
                }

                $po_value_data[] = array(
                    'product_option_value_id' => $pov['product_option_value_id'],
                    'option_value_id'         => $pov['option_value_id'],
                    'name'                    => $pov['name'],
                    'image'                   => $pov_image,
                    'quantity'                => 0,
                    'subtract'                => 0,
                    'price'                   => 0,
                    'price_prefix'            => '+',
                    'weight'                  => 0,
                    'weight_prefix'           => '+',

                    'selected'                => false,
                    'disabled_by_selection'   => false,
                );
            }

            if (!empty($po_value_data)) {
                $po_data[] = array(
                    'product_option_id'    => $po['product_option_id'],
                    'product_option_value' => $po_value_data,
                    'option_id'            => $po['option_id'],
                    'name'                 => $name,
                    'class'                => $po_class,
                    'type'                 => $po['type'],
                    'value'                => $po['value'],
                    'required'             => ($po['required']) ? true : false,
                );
            }

        }

        return $po_data;
    }

    public function getCombinationsForOptions($product_id, $options)
    {
        $selection_combinations = array();
        if (!is_array($options)) { return; }

        foreach ($options as $option) {
            if (isset($option['product_option_value'])
            && is_array($option['product_option_value'])) {


                foreach ($option['product_option_value'] as $option_value) {

                    $generated_statuses = array();
                    $connected_options = $this->getOptionsConnectedToOption(
                        $product_id, $option['option_id'], $option_value['option_value_id']);


                    foreach ($options as $o_k => $o_v) {
                        if (isset($o_v['product_option_value'])
                        && is_array($o_v['product_option_value'])) {
                            foreach ($o_v['product_option_value'] as $ov_k => $ov_v) {

                                $status = false;
                                foreach ($connected_options as $co) {
                                    if (($co['option_id'] == $o_v['option_id'])
                                    && ($co['option_value_id'] == $ov_v['option_value_id'])) {
                                        $status = true;
                                    }
                                }

                                if ($status && !$this->allow_buy_with_zero_quantity) {
                                    $only_fake_combination = $this->onlyFakeCombination($product_id,
                                        array('option_id' => $option['option_id'], 'option_value_id' => $option_value['option_value_id']),
                                        array('option_id' => $o_v['option_id'], 'option_value_id' => $ov_v['option_value_id'])
                                    );

                                    if ($only_fake_combination) {
                                        $status = false;
                                    }
                                }

                                $generated_statuses[$o_k]['product_option_value'][$ov_k] = $status;
                            }
                        }
                    }


                    $selection_combinations[] = array(
                        'active_option'         => $option['option_id'],
                        'active_option_value'   => $option_value['option_value_id'],
                        'generated_statuses'    => $generated_statuses
                    );
                }
            }
        }

        return $selection_combinations;
    }

    private function onlyFakeCombination($product_id, $option_a, $option_b)
    {
        $combinations_a = array();
        $combinations_for_check = array();
        $conna_q = $this->db->query("SELECT * FROM `" . DB_PREFIX . $this->db->escape(self::OPTION_CONNECTION) ."`
            WHERE `product_id` = '" . (int)$product_id . "'
            AND `option_a` = '" . (int)$option_a['option_id'] . "'
            AND `option_value_a` = '" . (int)$option_a['option_value_id'] . "'");

        foreach ($conna_q->rows as $comb) { $combinations_a[] = (int)$comb['combination_id']; }

        $connb_q = $this->db->query("SELECT * FROM `" . DB_PREFIX . $this->db->escape(self::OPTION_CONNECTION) ."`
            WHERE `product_id` = '" . (int)$product_id . "'
            AND `option_a` = '" . (int)$option_b['option_id'] . "'
            AND `option_value_a` = '" . (int)$option_b['option_value_id'] . "'");

        foreach ($connb_q->rows as $comb) {
            if (in_array($comb['combination_id'], $combinations_a)) {
                $combinations_for_check[] = $comb['combination_id'];
            }
        }

        $checker = true;
        foreach ($combinations_for_check as $c) {
            $comb_q = $this->db->query("SELECT * FROM `". DB_PREFIX . $this->db->escape(self::OPTION_COMMBINATION) ."`
            WHERE `product_id` = '". (int)$product_id ."'
            AND `combination_id` = '". (int)$c ."'");
            if ($comb_q->row && $comb_q->row['quantity'] > 0) {
                $checker = false;
            }
        }

        return $checker;
    }

    private function getOptionsConnectedToOption($product_id, $option_id, $option_value_id)
    {
        $connected_options = array();

        $comb_q = $this->db->query("SELECT * FROM `" . DB_PREFIX . $this->db->escape(self::OPTION_CONNECTION) ."`
            WHERE `product_id` = '" . (int)$product_id . "'
            AND `option_a` = '" . (int)$option_id . "'
            AND `option_value_a` = '" . (int)$option_value_id . "'");

        foreach ($comb_q->rows as $comb) {

            // GET OPTIONS FOR CONNECTION
            $options_q = $this->db->query("SELECT * FROM `" . DB_PREFIX . $this->db->escape(self::OPTION_CONNECTION) ."`
                WHERE `combination_id` = '" . (int)$comb['combination_id'] . "'
                AND `product_id` = '" . (int)$product_id . "'");

            foreach ($options_q->rows as $opt) {

                $connected_options[] = array(
                    'option_id'         => $opt['option_a'],
                    'option_value_id'   => $opt['option_value_a']
                );
            }
        }

        return $connected_options;
    }

    public function getDefaultValues($product_id, $data = array())
    {
        if (!$data) {
            $data = $this->model_catalog_product->getProduct($product_id);
        }
        $lowest_price = $this->getLowestPrice($product_id);

        $result = array(
            'price'         => 0,
            'special'       => false,
            'rating'        => 0,
            'min_quantity'  => $this->getMinQuantity($product_id),
            'max_quantity'  => $this->getMaxQuantity($product_id),
        );

        if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
            $result['price'] = round($this->tax->calculate($lowest_price, $data['tax_class_id'], $this->config->get('config_tax')), 0);
        }

        if ((float)$data['special']) {
            $result['special'] = round($this->tax->calculate($data['special'], $data['tax_class_id'], $this->config->get('config_tax')), 0);
        }

        if ($data['quantity'] <= 0) {
            $result['stock'] = $data['stock_status'];
        } elseif ($this->config->get('config_stock_display')) {
            $result['stock'] = $data['quantity'];
        } else {
            $result['stock'] = $this->language->get('text_instock');
        }

        $result['rating'] = (int)$data['rating'];

        return $result;
    }

    public function getLowestPrice($product_id)
    {
        return $this->super_offers->getLowestPrice($product_id);
    }

    public function getMinQuantity($product_id)
    {
        return $this->super_offers->getMinQuantity($product_id);
    }

    public function getMaxQuantity($product_id)
    {
        return $this->super_offers->getMaxQuantity($product_id);
    }

    /* ORDER FUNCTIONS */

    public function quantityHandler($order_id, $prefix)
    {
        $order_data = $this->getOrderData($order_id);
        foreach ($order_data as $product_data) {
            if (isset($product_data['product_id']) && isset($product_data['options'])
            && is_array($product_data['options']) && $product_data['options']) {
                if (!$this->isOptionsForProduct($product_data['product_id'])) { continue; }

                $combination = $this->super_offers->getCombinationForActiveOptions($product_data['product_id'], $product_data['options']);
                if ($combination !== Null) {

                    if ($combination['quantity'] != $this->super_offers->getNullValue()
                    && $combination['subtract']) {
                        $q = $this->_prefixHelper(intval($combination['quantity']), intval($product_data['quantity']), $prefix);
                        $this->changeCombinationQuantity($combination['combination_id'], $q);
                    }
                }
            }
        }
    }

    private function getOrderData($order_id)
    {
        $order_data = array();
        $order_products = $this->getOrderProducts($order_id);

        foreach ($order_products as $order_product) {

            $options = array();

            $order_options = $this->getOrderOptions($order_id, $order_product['order_product_id']);
            foreach ($order_options as $option) {

                $real = $this->getRealOptionIds($option['product_option_id'], $option['product_option_value_id']);
                if ($real['option_id'] !== False && $real['option_value_id'] !== False) {
                    $options[] = array(
                        'option_id'         => $real['option_id'],
                        'option_value_id'   => $real['option_value_id']
                    );
                }
            }

            $order_data[] = array(
                'product_id' => $order_product['product_id'],
                'options'    => $options,
                'quantity'   => $order_product['quantity']
            );
        }

        return $order_data;
    }

    private function getOrderProducts($order_id)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product`
            WHERE `order_id` = '" . (int)$order_id . "'");
        return $query->rows;
    }

    private function getOrderOptions($order_id, $order_product_id)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_option`
            WHERE `order_id` = '" . (int)$order_id . "'
            AND `order_product_id` = '" . (int)$order_product_id . "'");
        return $query->rows;
    }

    private function getRealOptionIds($fake_option_id, $fake_option_value_id)
    {
        $query = $this->db->query("SELECT `option_id`, `option_value_id` FROM `" . DB_PREFIX . "product_option_value`
            WHERE `product_option_id` = '" . (int)$fake_option_id . "'
            AND `product_option_value_id` = '" . (int)$fake_option_value_id . "'");

        return array(
            'option_id'         => isset($query->row['option_id']) ? $query->row['option_id'] : false,
            'option_value_id'   => isset($query->row['option_value_id']) ? $query->row['option_value_id'] : false,
        );
    }

    private function _prefixHelper($value, $newvalue, $prefix)
    {
        switch ($prefix) {
            case '+':
                $value += $newvalue;
                break;
            case '-':
                $value -= $newvalue;
                break;
        }

        return $value;
    }

    private function changeCombinationQuantity($combination_id, $quantity)
    {
        $this->db->query("UPDATE `". DB_PREFIX . $this->db->escape(self::OPTION_COMMBINATION) ."`
            SET `quantity` = '". $this->db->escape($quantity) ."'
            WHERE `combination_id` = '". (int)$combination_id ."'");
    }
}