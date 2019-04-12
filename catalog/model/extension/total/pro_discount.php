<?php
/*
 *  location: admin/model
 */
class ModelExtensionTotalProDiscount extends Model
{
    private $codename = 'pro_discount';
    private $route = 'extension/total/pro_discount';

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
        }
    }
}
