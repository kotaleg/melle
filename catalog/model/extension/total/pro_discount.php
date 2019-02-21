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

    public function getTotal($total)
    {
        $this->load->language('extension/total/sub_total');

        $sub_total = $this->cart->getSubTotal();

        if (!empty($this->session->data['vouchers'])) {
            foreach ($this->session->data['vouchers'] as $voucher) {
                $sub_total += $voucher['amount'];
            }
        }

        $total['totals'][] = array(
            'code'       => 'sub_total',
            'title'      => $this->language->get('text_sub_total'),
            'value'      => $sub_total,
            'sort_order' => $this->config->get('sub_total_sort_order')
        );

        $total['total'] += $sub_total;
    }
}
