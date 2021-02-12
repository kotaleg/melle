<?php

class pro_discount
{
    const DISCOUNT_TABLE = 'pd_discounts';
    const CATEGORY_TABLE = 'pd_category';
    const PRODUCT_TABLE = 'pd_product';
    const MANUFACTURER_TABLE = 'pd_manufacturer';
    const CUSTOMER_TABLE = 'pd_customer';

    const SKIDOSIK_TABLE = 'pd_skidosik';

    const SALE = 'sale';
    const SALE_COUNT = 'sale_count';

    const MONEY = 'money';
    const PERCENT = 'percent';

    private $codename = 'pro_discount';
    private $route = 'extension/total/pro_discount';

    function __construct($registry, $extra = array())
    {
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        $this->customer = $registry->get('customer');
        $this->session = $registry->get('session');

        // SUPER OFFERS START
        if ((in_array(__FUNCTION__, array('__construct'))) && !isset($this->super_offers)
        && file_exists(DIR_SYSTEM.'library/pro_hamster/extension/super_offers.json')) {
            $this->super_offers = new \super_offers($registry);
        }
        // SUPER OFFERS END
    }

    public function parseProducts($products_data)
    {
        $this->session->data["{$this->codename}_cart"] = array();

        foreach ($products_data as $pd_key => $pd) {
            if (!isset($pd['product_id'])) { continue; }

            if (isset($this->session->data["{$this->codename}_cart"][$pd['product_id']])) {
                $this->session->data["{$this->codename}_cart"][$pd['product_id']]['q'] += $pd['quantity'];
                $this->session->data["{$this->codename}_cart"][$pd['product_id']]['t'] += $pd['total'];
            } else {
                $this->session->data["{$this->codename}_cart"][$pd['product_id']]['q'] = $pd['quantity'];
                $this->session->data["{$this->codename}_cart"][$pd['product_id']]['t'] = $pd['total'];

                // U - for "used"
                $this->session->data["{$this->codename}_cart"][$pd['product_id']]['u'] = 0;
            }
        }

        $this->initSkidosik();
    }

    public function getCartProductTotal($product_id)
    {
        if (isset($this->session->data["{$this->codename}_cart"][$product_id]['t'])) {
            return $this->session->data["{$this->codename}_cart"][$product_id]['t'];
        }
        return false;
    }

    public function getCartProductQuantity($product_id)
    {
        if (isset($this->session->data["{$this->codename}_cart"][$product_id]['q'])) {
            return (int)$this->session->data["{$this->codename}_cart"][$product_id]['q'];
        }
        return false;
    }

    public function getCartProductUsed($product_id)
    {
        if (isset($this->session->data["{$this->codename}_cart"][$product_id]['u'])) {
            return (int)$this->session->data["{$this->codename}_cart"][$product_id]['u'];
        }
        return false;
    }

    public function updateCartProductUsed($product_id, $count)
    {
        if (isset($this->session->data["{$this->codename}_cart"][$product_id]['u'])) {
            $this->session->data["{$this->codename}_cart"][$product_id]['u'] += $used;
        } else {
            $this->session->data["{$this->codename}_cart"][$product_id]['u'] = $used;
        }
    }

    private function initSkidosik()
    {
        $this->db->query("DELETE FROM ". DB_PREFIX . self::SKIDOSIK_TABLE ."
            WHERE `session_id` = '". $this->db->escape($this->session->getId()) ."'");
    }

    public function updateSkidosik($value)
    {
        $this->db->query("INSERT INTO ". DB_PREFIX . self::SKIDOSIK_TABLE ."
            (`session_id`, `value`)
            VALUES (
                '". $this->db->escape($this->session->getId()) ."',
                '". $this->db->escape($value) ."'
            )
            ON DUPLICATE KEY UPDATE
               `value` = `value` + VALUES(`value`);");
    }

    public function getSkidosik()
    {
        $q = $this->db->query("SELECT `value` FROM ". DB_PREFIX . self::SKIDOSIK_TABLE ."
            WHERE `session_id` = '". $this->db->escape($this->session->getId()) ."'");

        if ($q->num_rows) {
            return $q->row['value'];
        }
        return 0;
    }

    public function getSpecialPrice($product_id, $special, $use_cart = Null, $text = Null)
    {
        $new_special = $special;

        $discounts = $this->getDiscounts(self::SALE);

        $product_info = $this->getProduct($product_id);
        if (!$product_info) { return $special; }

        $customer_id = $this->customer->getId();

        foreach ($discounts as $k => $discount) {
            // REGISTERED ONLY
            if ($discount['registered_only']) {
                if (!$customer_id) {
                    unset($discounts[$k]);
                    continue;
                }
            }

            $sum = true;
            $count = true;

            // START_SUM
            if ($use_cart !== Null && $discount['start_sum'] > 0) {
                $ct = $this->getCartProductTotal($product_id);

                if ($ct !== false && $ct < $discount['start_sum']) {
                    $sum = false;
                }
            }

            // START_COUNT
            if ($use_cart !== Null
            && $discount['start_count'] > 0) {
                $cq = $this->getCartProductQuantity($product_id);

                if ($cq !== False && $cq > 0) {
                    if ($cq < $discount['start_count']) {
                        $count = false;
                    } else {
                        // if ($cq % $discount['start_count'] !== 0) {
                        //     $count = false;
                        // }
                    }
                }

            }

            if (!$sum || !$count) {
                if ($discount['sum_and_count']) {
                    unset($discounts[$k]);
                    continue;
                }
            }

            if (!$sum && !$count) {
                unset($discounts[$k]);
                continue;
            }

            $extra = $this->getDiscountExtra($discount['discount_id']);

            // CUSTOMERS
            if ($extra['customers']) {
                if (!in_array($customer_id, $extra['customers'])) {
                    unset($discounts[$k]);
                    continue;
                }
            }

            // PRODUCTS
            if ($extra['products']) {
                if (!in_array($product_id, $extra['products'])) {
                    unset($discounts[$k]);
                    continue;
                }
            }

            // MANUFACTURERS
            if ($extra['manufacturers']) {
                if (!in_array($product_info['manufacturer_id'], $extra['manufacturers'])) {
                    unset($discounts[$k]);
                    continue;
                }
            }

            // CATEGORIES
            if ($extra['categories']) {
                $check = false;
                $cats = $this->getAllCategoriesForProduct($product_id);

                foreach ($extra['categories'] as $c) {
                    if (in_array($c, $cats)) {
                        $check = true;
                    }
                }

                if ($check === false) {
                    unset($discounts[$k]);
                    continue;
                }
            }
        }

        if (!$discounts) { return $new_special; }

        // SUPER OFFERS START
        if ((in_array(__FUNCTION__, array('getSpecialPrice')))
        && file_exists(DIR_SYSTEM.'library/pro_hamster/extension/super_offers.json')) {
            $product_info['price'] = $this->super_offers->getLowestPrice($product_id);
        }
        // SUPER OFFERS END

        if ($product_info['price'] <= 0) { return $new_special; }

        $discount = array_shift($discounts);

        // DIS
        $available_count = 0;
        if ($use_cart !== Null
        && $discount['start_count'] > 0) {
            $cq = $this->getCartProductQuantity($product_id);
            if ($cq !== False && $cq > 0) {
                if ($cq >= $discount['start_count']) {
                    $div = $cq % $discount['start_count'];
                    $used = $this->getCartProductUsed($product_id);

                    $available_count = ($cq - $div) - $used;
                }
            }
        }

        // PERCENT
        if ($discount['sign'] === self::PERCENT && $discount['value'] > 0) {
            $product_info['price'] = $product_info['price'] -
                (($product_info['price']/100)*$discount['value']);
        }

        // MONEY
        if ($discount['sign'] === self::MONEY) {
            $product_info['price'] = $product_info['price'] - $discount['value'];
        }

        if ($product_info['price'] > 0) {
            $new_special = $product_info['price'];
        }

        if ($text !== Null) {
            if ($discount['sum_and_count']
            && $discount['start_count'] > 0
            && $discount['sign'] == self::PERCENT) {
                return array(
                    'start_count' => $discount['start_count'],
                    'value' => $discount['value'],
                );
            }
        }

        if ($use_cart !== Null) {
            return array(
                'available_count' => $available_count,
                'special' => $new_special,
            );
        }

        return $new_special;
    }

    public function getSpecialText($product_id, $full = true)
    {
        $text = false;

        $special = $this->getSpecialPrice($product_id, 0, Null, true);
        if ($special && isset($special['start_count'])) {
            $text = "* при покупке {$special['start_count']} шт.";

            if ($full) {
                $text .= " -{$special['value']}%";
            }
        }

        $total = $this->getTotal($product_id, 10000000, 0, Null);
        if ($total && isset($total['count_like'])) {
            $text = "{$total['count']} по цене {$total['count_like']}";
        }

        return $text;
    }

    public function fixTotal($products_data)
    {
        foreach ($products_data as $pd_key => $pd) {

            if (isset($pd['product_id']) && isset($pd['quantity'])) {

                $special = $this->getSpecialPrice($pd['product_id'], 0, true);
                $total_data = $this->getTotal($pd['product_id'], $pd['quantity'], $pd['price']);

                if ($total_data !== null) {
                    $full_price = $total_data['price'];
                    $shitty_quantity = $pd['quantity'] - $total_data['count'];
                    if ($shitty_quantity) {
                        $full_price += $shitty_quantity * $pd['price'];
                    }
                    $products_data[$pd_key]['total'] = $full_price;
                } else {
                    if ($special) {
                        if (isset($special['available_count']) && $special['available_count']) {
                            $products_data[$pd_key]['total'] = 0;

                            for ($i=1; $i <= $pd['quantity']; $i++) {
                                if ($i <= $special['available_count']) {
                                    $products_data[$pd_key]['total'] += $special['special'];

                                    if ($pd['price'] > 0 && $pd['price'] > $special['special']) {
                                        $this->updateSkidosik(($pd['price'] - $special['special']));
                                    }
                                } else {
                                    $products_data[$pd_key]['total'] += $pd['price'];
                                }
                            }
                        } else {
                            $products_data[$pd_key]['price'] = $special['special'];
                            $products_data[$pd_key]['total'] = $special['special'] * $pd['quantity'];
                        }
                    }
                }

            }
        }

        return $products_data;
    }

    private function getTotal($product_id, $quantity, $price)
    {
        $total = null;

        $discounts = $this->getDiscounts(self::SALE_COUNT);
        $customer_id = $this->customer->getId();

        $product_info = $this->getProduct($product_id);
        if (!$product_info) { return $price; }

        foreach ($discounts as $k => $discount) {

            if ($quantity < $discount['products_count']) {
                unset($discounts[$k]);
                continue;
            }

            // REGISTERED ONLY
            if ($discount['registered_only']) {
                if (!$customer_id) {
                    unset($discounts[$k]);
                    continue;
                }
            }

            $extra = $this->getDiscountExtra($discount['discount_id']);

            // CUSTOMERS
            if ($extra['customers']) {
                if (!in_array($customer_id, $extra['customers'])) {
                    unset($discounts[$k]);
                    continue;
                }
            }

            // PRODUCTS
            if ($extra['products']) {
                if (!in_array($product_id, $extra['products'])) {
                    unset($discounts[$k]);
                    continue;
                }
            }

            // MANUFACTURERS
            if ($extra['manufacturers']) {
                if (!in_array($product_info['manufacturer_id'], $extra['manufacturers'])) {
                    unset($discounts[$k]);
                    continue;
                }
            }

            // CATEGORIES
            if ($extra['categories']) {
                $check = false;
                $cats = $this->getAllCategoriesForProduct($product_id);

                foreach ($extra['categories'] as $c) {
                    if (in_array($c, $cats)) {
                        $check = true;
                    }
                }

                if ($check === false) {
                    unset($discounts[$k]);
                    continue;
                }
            }
        }

        if (!$discounts) { return $total; }
        $discount = array_shift($discounts);

        $total = array(
            'count' => $discount['products_count'],
            'count_like' => $discount['count_like'],
            'price' => $price * $discount['count_like'],
        );

        return $total;
    }

    public function getDiscounts($type = null)
    {
        $discounts = array();

        $sql = "SELECT *
            FROM `". DB_PREFIX . self::DISCOUNT_TABLE ."`
            WHERE `status` = '". true ."'
            AND `value` > '0'";

        if ($type !== null) {
            $sql .= " AND `type` = '". $this->db->escape($type) ."' ";
        }

        $sql .= " AND `start_date` <= NOW() ";
        $sql .= " AND NOW() <= `finish_date` ";
        $sql .= " ORDER BY `sort_order` ASC ";

        $q = $this->db->query($sql);

        foreach ($q->rows as $item) {

            $item['status'] = (bool)$item['status'];
            $item['registered_only'] = (bool)$item['registered_only'];
            $item['sum_and_count'] = (bool)$item['sum_and_count'];

            if (!empty($item['start_date'])) {
                $item['start_date'] = strtotime($item['start_date']);
            }

            if (!empty($item['finish_date'])) {
                $item['finish_date'] = strtotime($item['finish_date']);
            }

            $discounts[] = $item;
        }

        return $discounts;
    }

    public function getDiscountExtra($discount_id)
    {
        $extra = array();

        $extra['categories'] = array();
        $extra['products'] = array();
        $extra['manufacturers'] = array();
        $extra['customers'] = array();

        // CATEGORY
        $category_q = $this->db->query("SELECT dd.category_id AS id
            FROM `". DB_PREFIX . self::CATEGORY_TABLE ."` dd
            WHERE dd.discount_id = '" . (int)$discount_id . "'");

        foreach ($category_q->rows as $item) {
            $extra['categories'][] = (int)$item['id'];
        }

        // PRODUCT
        $product_q = $this->db->query("SELECT dd.product_id AS id
            FROM `". DB_PREFIX . self::PRODUCT_TABLE ."` dd
            WHERE dd.discount_id = '" . (int)$discount_id . "'");

        foreach ($product_q->rows as $item) {
            $extra['products'][] = $item['id'];
        }

        // MANUFACTURER
        $manufacturer_q = $this->db->query("SELECT dd.manufacturer_id AS id
            FROM `". DB_PREFIX . self::MANUFACTURER_TABLE ."` dd
            WHERE dd.discount_id = '" . (int)$discount_id . "'");

        foreach ($manufacturer_q->rows as $item) {
            $extra['manufacturers'][] = (int)$item['id'];
        }

        // CUSTOMER
        $customer_q = $this->db->query("SELECT dd.customer_id AS id
            FROM `". DB_PREFIX . self::CUSTOMER_TABLE ."` dd
            WHERE dd.discount_id = '" . (int)$discount_id . "'");

        foreach ($customer_q->rows as $item) {
            $extra['customers'][] = (int)$item['id'];
        }

        return $extra;
    }

    public function getProduct($product_id)
    {
        $query = $this->db->query("SELECT DISTINCT *, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int)$product_id . "'");

        if ($query->num_rows) {
            return array(
                'product_id'       => $query->row['product_id'],
                'quantity'         => $query->row['quantity'],
                'manufacturer_id'  => $query->row['manufacturer_id'],
                'price'            => ($query->row['discount'] ? $query->row['discount'] : $query->row['price']),
                'special'          => $query->row['special'],
                'tax_class_id'     => $query->row['tax_class_id'],
                'date_available'   => $query->row['date_available'],
                'status'           => $query->row['status'],
            );
        } else {
            return false;
        }
    }

    public function getAllCategoriesForProduct($product_id)
    {
        $categories = array();

        $q = $this->db->query("SELECT DISTINCT category_id
            FROM `". DB_PREFIX . "product_to_category`
            WHERE product_id = '". $product_id ."'");

        foreach ($q->rows as $v) {
            $categories[] = $v['category_id'];
        }

        $categories = array_unique($categories);
        foreach ($categories as $c) {
            $q = $this->db->query("SELECT DISTINCT path_id
                FROM `". DB_PREFIX . "category_path`
                WHERE category_id = '". $c ."'");

            foreach ($q->rows as $v) {
                $categories[] = $v['path_id'];
            }
        }

        return array_unique($categories);
    }
}
