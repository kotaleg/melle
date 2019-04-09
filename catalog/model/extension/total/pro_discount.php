<?php
/*
 *  location: admin/model
 */
class ModelExtensionTotalProDiscount extends Model
{
    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/setting');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/db');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);

        $this->pro_discount = new \pro_discount($registry);
    }

    public function getSpecialPrice($product_id, $special, $use_cart = Null)
    {
        return $this->pro_discount->getSpecialPrice($product_id, $special, $use_cart);
    }

    public function getSpecialText($product_id)
    {
        return $this->pro_discount->getSpecialText($product_id);
    }

    public function getTotal($total)
    {
        //
    }
}
