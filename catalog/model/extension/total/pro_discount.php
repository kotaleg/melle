<?php
/*
 *  location: catalog/model
 */
class ModelExtensionTotalProDiscount extends Model
{
    private $codename = 'pro_discount';
    private $route = 'extension/total/pro_discount';

    const DISCOUNT_TABLE = 'pd_discounts';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language($this->route);
        $this->load->model('extension/pro_patch/setting');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/db');

        $this->pro_discount = new \pro_discount($registry);
    }

    public function getSpecialPrice($product_id, $special, $use_cart = Null)
    {
        return $this->pro_discount->getSpecialPrice($product_id, $special, $use_cart);
    }

    public function getSpecialText($product_id, $full = true)
    {
        return $this->pro_discount->getSpecialText($product_id, $full);
    }

    public function getTotal($total)
    {
        $skidosik = $this->pro_discount->getSkidosik();

        if ($skidosik > 0) {
            $total['totals'][] = array(
                'code'       => $this->codename,
                'title'      => $this->language->get('text_pro_discount'),
                'value'      => $skidosik,
                'sort_order' => $this->config->get("total_{$this->codename}_sort_order"),
            );

            $total['total'] -= $skidosik;
        }
    }

    public function getDiscountData($discount_id)
    {
        $discount = array();

        $q = $this->db->query("SELECT *
            FROM `". DB_PREFIX . self::DISCOUNT_TABLE ."`
            WHERE `discount_id` = '" . (int)$discount_id . "'
            AND `status` = '". true ."'
            AND `start_date` <= NOW()
            AND NOW() <= `finish_date`");

        if (isset($q->row['discount_id'])) {
            $discount['discount_id'] = (int)$q->row['discount_id'];

            $discount['type'] = (string)$q->row['type'];

            $discount['sort_order'] = (int)$q->row['sort_order'];
            $discount['status'] = (bool)$q->row['status'];
            $discount['name'] = (string)$q->row['name'];

            $discount['meta_title'] = (string)$q->row['meta_title'];
            $discount['meta_description'] = (string)$q->row['meta_description'];
            $discount['meta_keywords'] = (string)$q->row['meta_keywords'];

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
}
