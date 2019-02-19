<?php
class ControllerExtensionTotalDiscountSales extends Controller
{
    private $error = array();
    private $this_version = '1.9.0.0';
    private $this_extension = 'ocext_discountsales';
    private $this_ocext_host = 'oc2101.ocext';

    private $route = 'extension/total/discountsales';
    private $type = 'total';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/url');
        $this->load->model('extension/pro_patch/load');
        $this->load->model('extension/pro_patch/setting');
    }

    public function index()
    {
        $this->load->language($this->route);
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {

            if(isset($this->request->get['category'])){
                $post = $this->getDiscountsalesSortArray($this->request->post);
                $this->model_extension_pro_patch_setting->editSetting($this->type, 'discountsales_category', $post);
                $this->session->data['success'] = $this->language->get('text_success');
                $this->response->redirect($this->model_extension_pro_patch_url->link($this->route));
            }
            if(isset($this->request->get['manufacturer'])){
                $post = $this->getDiscountsalesSortArray($this->request->post);
                $this->model_extension_pro_patch_setting->editSetting($this->type, 'discountsales_manufacturer', $post);
                $this->session->data['success'] = $this->language->get('text_success');
                $this->response->redirect($this->model_extension_pro_patch_url->link($this->route));
            }
            if(isset($this->request->get['complect'])){
                $post = $this->getDiscountsalesComplectArray($this->request->post);
                $this->model_extension_pro_patch_setting->editSetting($this->type, 'discountsales_complect', $post);
                $this->session->data['success'] = $this->language->get('text_success');
                $this->response->redirect($this->model_extension_pro_patch_url->link($this->route));
            }
            if(isset($this->request->get['promo'])){

                $discountsales_disable_data = array();
                if(isset($this->request->post['discountsales_disable_data_categories'])){
                    $discountsales_disable_data['discountsales_disable_data_categories'] = $this->getDisableData($this->request->post['discountsales_disable_data_categories']);
                }
                if($discountsales_disable_data){
                    $this->model_extension_pro_patch_setting->editSetting($this->type, 'discountsales_disable_data', $discountsales_disable_data);
                }
                $this->model_extension_pro_patch_setting->editSetting($this->type, 'discountsales_promo', $this->request->post);
                $this->session->data['success'] = $this->language->get('text_success');
                $this->response->redirect($this->model_extension_pro_patch_url->link($this->route));
            }

            $post = $this->getDiscountsalesSortArray($this->request->post);
            $this->model_extension_pro_patch_setting->editSetting($this->type, 'discountsales', $post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->model_extension_pro_patch_url->link($this->route));
        }

        $lang = $this->config->get( 'config_admin_language' );
        $data['expamples'] = FALSE;
        if($lang=='ru'){
            $data['expamples'] = TRUE;
        }

        $data['payments'] = $this->gepPayments();
        $this->load->language($this->route);
        //Получаем налоги
        $this->load->model('localisation/tax_class');
        $data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();
        //Текст и ссылки
        $data['back'] = $this->model_extension_pro_patch_url->link('common/dashboard');
        $data['button_back'] = $this->language->get( 'button_back' );

        //Сообщения
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->language->get('text_success');
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        //Возвращаем в объект язык модуля
        $this->load->language($this->route);
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_none'] = $this->language->get('text_none');
        $data['text_money'] = $this->language->get('text_money');
        $data['text_percent'] = $this->language->get('text_percent');
        $data['text_none_all_payments'] = $this->language->get('text_none_all_payments');
        $data['text_plus'] = $this->language->get('text_plus');
        $data['text_minus'] = $this->language->get('text_minus');
        $data['text_examples'] = $this->language->get('text_examples');
        $data['text_examples_1_an'] = $this->language->get('text_examples_1_an');
        $data['text_examples_1_des'] = $this->language->get('text_examples_1_des');
        $data['text_examples_2_an'] = $this->language->get('text_examples_2_an');
        $data['text_examples_2_des'] = $this->language->get('text_examples_2_des');
        $data['text_enable'] = $this->language->get('text_enable');
        $data['tab_manufacturer'] = $this->language->get('tab_manufacturer');
        $data['tab_category'] = $this->language->get('tab_category');
        $data['tab_complect'] = $this->language->get('tab_complect');
        $data['tab_sales'] = $this->language->get('tab_sales');
        $data['text_quantity_products'] = $this->language->get('text_quantity_products');
        $data['text_new_price'] = $this->language->get('text_new_price');
        $data['entry_sales_limit_input'] = $this->language->get('entry_sales_limit_input');

        $data['tab_documentation'] = $this->language->get('tab_documentation');
        $data['tab_promo'] = $this->language->get('tab_promo');

        $data['entry_sales_limitation'] = $this->language->get('entry_sales_limitation');
        $data['entry_sales_limitation_info'] = $this->language->get('entry_sales_limitation_info');
        $data['entry_payment'] = $this->language->get('entry_payment');
        $data['entry_delta'] = $this->language->get('entry_delta');
        $data['entry_tax_class'] = $this->language->get('entry_tax_class');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_category'] = $this->language->get('entry_category');
        $data['entry_complect'] = $this->language->get('entry_complect');
        $data['entry_manufacturer'] = $this->language->get('entry_manufacturer');
        $data['entry_select'] = $this->language->get('entry_select');
        $data['entry_status_together_disabled'] = $this->language->get('entry_status_together_disabled');
        $data['entry_status_together'] = $this->language->get('entry_status_together');
        $data['entry_complect_product'] = $this->language->get('entry_complect_product');
        $data['entry_complect_quantity'] = $this->language->get('entry_complect_quantity');
        $data['entry_complect_quantity_info'] = $this->language->get('entry_complect_quantity_info');
        $data['entry_name_complect'] = $this->language->get('entry_name_complect');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');

        $data['entry_promo_category'] = $this->language->get('entry_promo_category');
        $data['entry_promo_discount'] = $this->language->get('entry_promo_discount');
        $data['entry_promo_manufacturer'] = $this->language->get('entry_promo_manufacturer');
        $data['entry_promo_to_product'] = $this->language->get('entry_promo_to_product');

        $data['entry_promo_manufacturer_position'] = $this->language->get('entry_promo_manufacturer_position');
        $data['entry_promo_manufacturer_position_on_image'] = $this->language->get('entry_promo_manufacturer_position_on_image');
        $data['entry_promo_manufacturer_position_up_h4'] = $this->language->get('entry_promo_manufacturer_position_up_h4');
        $data['entry_promo_manufacturer_position_css'] = $this->language->get('entry_promo_manufacturer_position_css');
        $data['entry_promo_manufacturer_position_css_info'] = $this->language->get('entry_promo_manufacturer_position_css_info');
        $data['entry_promo_manufacturer_color'] = $this->language->get('entry_promo_manufacturer_color');
        $data['entry_promo_manufacturer_text'] = $this->language->get('entry_promo_manufacturer_text');

        $data['entry_apply_any_am_ord_link'] = $this->language->get('entry_apply_any_am_ord_link');

        $data['entry_date_start'] = $this->language->get('entry_date_start');
        $data['entry_date_finish'] = $this->language->get('entry_date_finish');
        $data['entry_complect_disable_other_discount'] = $this->language->get('entry_complect_disable_other_discount');
        $data['entry_disable_on_specprice'] = $this->language->get('entry_disable_on_specprice');
        $data['entry_disable_on_this_categories'] = $this->language->get('entry_disable_on_this_categories');
        $data['entry_disable_on_this_categories_info'] = $this->language->get('entry_disable_on_this_categories_info');
        $data['entry_disable_on_this_categories_name'] = $this->language->get('entry_disable_on_this_categories_name');
        $data['entry_disable_need_create'] = $this->language->get('entry_disable_need_create');

        $data['entry_status_all'] = $this->language->get('entry_status_all');
        $data['entry_status_exp'] = $this->language->get('entry_status_exp');

        $data['text_loading_notifications'] = $this->getNotifications();

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_add_discount'] = $this->language->get('button_add_discount');
        $data['button_remove'] = $this->language->get('button_remove');

        //хлебные крошки
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->model_extension_pro_patch_url->link('common/home')
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_total'),
            'href' => $this->model_extension_pro_patch_url->getExtensionAjax('total')
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->model_extension_pro_patch_url->link($this->route)
        );

        $data['action'] = $this->model_extension_pro_patch_url->link($this->route);
        $data['action_manufacturer'] = $this->model_extension_pro_patch_url->link($this->route, '&manufacturer');
        $data['action_category'] = $this->model_extension_pro_patch_url->link($this->route, '&category');
        $data['action_complect'] = $this->model_extension_pro_patch_url->link($this->route, '&complect');
        $data['action_promo'] = $this->model_extension_pro_patch_url->link($this->route, '&promo');


        if (isset($this->request->post['discountsales_discount'])) {
            $data['discountsales_discount'] = $this->request->post['discountsales_discount'];
        } elseif($this->config->get('discountsales_discount')) {
            $data['discountsales_discount'] = $this->config->get('discountsales_discount');
        }else{
            $data['discountsales_discount'] = array();
        }
        if($data['discountsales_discount']){
            usort($data['discountsales_discount'], array('ControllerExtensionTotalDiscountSales','cmpSortArrayWhisDiscounts'));
        }

        if (isset($this->request->post['discountsales_status'])) {
            $data['discountsales_status'] = $this->request->post['discountsales_status'];
        } else {
            $data['discountsales_status'] = $this->config->get('discountsales_status');
        }

        $data['discountsales_discount_status'] = 0;

        if (isset($this->request->post['discountsales_discount_status'])) {
            $data['discountsales_discount_status'] = $this->request->post['discountsales_discount_status'];
        } else {
            $data['discountsales_discount_status'] = $this->config->get('discountsales_discount_status');
        }

        $data['discountsales_disable_data_categories'] = array();
        if($this->config->get('discountsales_disable_data_categories')){
            $data['discountsales_disable_data_categories'] = $this->config->get('discountsales_disable_data_categories');
        }

        if (isset($this->request->post['discountsales_status_together'])) {
            $data['discountsales_status_together'] = $this->request->post['discountsales_status_together'];
        } else {
            $data['discountsales_status_together'] = $this->config->get('discountsales_status_together');
        }

        $data['discountsales_sort_order'] = 0;
        if($this->config->get('discountsales_sort_order')){
            $data['discountsales_sort_order'] = $this->config->get('discountsales_sort_order');
        }
        $data['discountsales_complect_sort_order'] = 0;
        if($this->config->get('discountsales_complect_sort_order')){
            $data['discountsales_complect_sort_order'] = $this->config->get('discountsales_complect_sort_order');
        }
        $data['discountsales_manufacturer_sort_order'] = 0;
        if($this->config->get('discountsales_manufacturer_sort_order')){
            $data['discountsales_manufacturer_sort_order'] = $this->config->get('discountsales_manufacturer_sort_order');
        }
        $data['discountsales_category_sort_order'] = 0;
        if($this->config->get('discountsales_category_sort_order')){
            $data['discountsales_category_sort_order'] = $this->config->get('discountsales_category_sort_order');
        }

        $data['discountsales_promo'] = array();
        $data['discountsales_promo']['manufacturer']['text'] = 'Sale';
        $data['discountsales_promo']['manufacturer']['colour'] = '#23a1d1';
        $data['discountsales_promo']['category']['text'] = 'Sale';
        $data['discountsales_promo']['category']['colour'] = '#23a1d1';
        $data['discountsales_promo']['discount']['text'] = 'Sale';
        $data['discountsales_promo']['discount']['colour'] = '#23a1d1';
        $data['discountsales_promo']['to_product']['text'] = 'Sale';
        $data['discountsales_promo']['to_product']['colour'] = '#23a1d1';
        if($this->config->get('discountsales_promo')){
            $data['discountsales_promo'] = $this->config->get('discountsales_promo');
        }


        /////////////////////////////////////////category
        if (isset($this->request->post['discountsales_category_status'])) {
            $data['discountsales_category_status'] = $this->request->post['discountsales_category_status'];
        } else {
            $data['discountsales_category_status'] = $this->config->get('discountsales_category_status');
        }
        $data['discountsales_category_discount'] = array();
        if (isset($this->request->post['discountsales_category_discount'])) {
            $data['discountsales_category_discount'] = $this->request->post['discountsales_category_discount'];
        } elseif($this->config->get('discountsales_category_discount')) {
            $data['discountsales_category_discount'] = $this->config->get('discountsales_category_discount');
        }
        if($data['discountsales_category_discount']){
            usort($data['discountsales_category_discount'], array('ControllerExtensionTotalDiscountSales','cmpSortArrayWhisDiscounts'));
        }

        $this->load->model('catalog/category');
        $categories = $this->model_catalog_category->getCategories(0);
        if($categories){
            foreach ($categories as $category) {
                $category['name'] = str_replace(array("'",'"'), array(" ",' '), $category['name']);
                $data['categories'][$category['category_id']] = $category;
            }
        }else{
            $data['categories'] = array();
        }
        if (isset($this->request->post['discountsales_category_status_together'])) {
            $data['discountsales_category_status_together'] = $this->request->post['discountsales_category_status_together'];
        } else {
            $data['discountsales_category_status_together'] = $this->config->get('discountsales_category_status_together');
        }

        /////////////////////////////////////////manufacturer
        if (isset($this->request->post['discountsales_manufacturer_status'])) {
            $data['discountsales_manufacturer_status'] = $this->request->post['discountsales_manufacturer_status'];
        } else {
            $data['discountsales_manufacturer_status'] = $this->config->get('discountsales_manufacturer_status');
        }
        $data['discountsales_manufacturer_discount'] = array();
        if (isset($this->request->post['discountsales_manufacturer_discount'])) {
            $data['discountsales_manufacturer_discount'] = $this->request->post['discountsales_manufacturer_discount'];
        } elseif($this->config->get('discountsales_manufacturer_discount')) {
            $data['discountsales_manufacturer_discount'] = $this->config->get('discountsales_manufacturer_discount');
        }
        if($data['discountsales_manufacturer_discount']){
            usort($data['discountsales_manufacturer_discount'], array('ControllerExtensionTotalDiscountSales','cmpSortArrayWhisDiscounts'));
        }
        $this->load->model('catalog/manufacturer');
        $filter_data = array(
            'sort'  => 'name',
            'order' => 'ASC',
            'start' => 0,
            'limit' => 10000
        );
        $manufacturers = $this->model_catalog_manufacturer->getManufacturers($filter_data);
        if($manufacturers){
            foreach ($manufacturers as $manufacturer) {
                $manufacturer['name'] = str_replace(array("'",'"'), array(" ",' '), $manufacturer['name']);
                $data['manufacturers'][$manufacturer['manufacturer_id']] = $manufacturer;
            }
        }else{
            $data['manufacturers'] = array();
        }
        if (isset($this->request->post['discountsales_manufacturer_status_together'])) {
            $data['discountsales_manufacturer_status_together'] = $this->request->post['discountsales_manufacturer_status_together'];
        } else {
            $data['discountsales_manufacturer_status_together'] = $this->config->get('discountsales_manufacturer_status_together');
        }

        /////////////////////////////////////////complect
        if (isset($this->request->post['discountsales_complect_status'])) {
            $data['discountsales_complect_status'] = $this->request->post['discountsales_complect_status'];
        } else {
            $data['discountsales_complect_status'] = $this->config->get('discountsales_complect_status');
        }
        $data['discountsales_complect_discount'] = array();
        if (isset($this->request->post['discountsales_complect_discount'])) {
            $data['discountsales_complect_discount'] = $this->request->post['discountsales_complect_discount'];
        } elseif($this->config->get('discountsales_complect_discount')) {
            $data['discountsales_complect_discount'] = $this->config->get('discountsales_complect_discount');
        }

        if($data['discountsales_complect_discount']){
            usort($data['discountsales_complect_discount'], array('ControllerExtensionTotalDiscountSales','cmpSortArrayWhisDiscounts'));
        }
        if (isset($this->request->post['discountsales_complect_status_together'])) {
            $data['discountsales_complect_status_together'] = $this->request->post['discountsales_complect_status_together'];
        } else {
            $data['discountsales_complect_status_together'] = $this->config->get('discountsales_complect_status_together');
        }

        $data['product_autocomplete_link'] = $this->model_extension_pro_patch_url->ajax('catalog/product/autocomplete');
        $data['get_notifications_link'] = $this->model_extension_pro_patch_url->ajax('extension/total/discountsales/getNotifications');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->model_extension_pro_patch_load->view($this->route, $data));
    }

    private function validate()
    {
        if (!$this->user->hasPermission('modify', $this->route)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if(isset($this->request->post['discountsales_discount'])){
            foreach ($this->request->post['discountsales_discount'] as $key => $discountsales_discount) {
                foreach ($discountsales_discount as $field => $value) {

                    if($field=='sales_limitation'){
                        $this->request->post['discountsales_discount'][$key]['sales_limitation'] = $this->getFloat($value);
                    }
                    if($field=='value_data'){
                        $this->request->post['discountsales_discount'][$key]['value_data'] = $this->getFloat($value);
                    }
                }
            }
        }elseif (isset($this->request->post['discountsales_category_discount'])) {
            foreach ($this->request->post['discountsales_category_discount'] as $key => $discountsales_discount) {
                foreach ($discountsales_discount as $field => $value) {

                    if($field=='sales_limitation'){
                        $this->request->post['discountsales_category_discount'][$key]['sales_limitation'] = $this->getFloat($value);
                    }
                    if($field=='value_data'){
                        $this->request->post['discountsales_category_discount'][$key]['value_data'] = $this->getFloat($value);
                    }
                }
            }
        }elseif (isset($this->request->post['discountsales_manufacturer_discount'])) {
            foreach ($this->request->post['discountsales_manufacturer_discount'] as $key => $discountsales_discount) {
                foreach ($discountsales_discount as $field => $value) {

                    if($field=='sales_limitation'){
                        $this->request->post['discountsales_manufacturer_discount'][$key]['sales_limitation'] = $this->getFloat($value);
                    }
                    if($field=='value_data'){
                        $this->request->post['discountsales_manufacturer_discount'][$key]['value_data'] = $this->getFloat($value);
                    }
                }
            }
        }elseif (isset($this->request->post['discountsales_complect_discount'])) {
            foreach ($this->request->post['discountsales_complect_discount'] as $key => $discountsales_discount) {
                foreach ($discountsales_discount as $field => $value) {

                    if($field=='sales_limitation'){
                        $this->request->post['discountsales_complect_discount'][$key]['sales_limitation'] = $this->getFloat($value);
                    }
                    if($field=='value_data'){
                        $this->request->post['discountsales_complect_discount'][$key]['value_data'] = $this->getFloat($value);
                    }
                }
            }
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    private function getDiscountsalesComplectArray($post)
    {
        if(isset($post['discountsales_complect_discount']) && $post['discountsales_complect_discount']){
            foreach($post['discountsales_complect_discount'] as $key=>$discountsales_complect_discount){
                $id_container = '';
                $discounts_sort = '';
                if(isset($discountsales_complect_discount['products']) && $discountsales_complect_discount['products']){
                    $products = $discountsales_complect_discount['products'];
                    ksort($products);
                    foreach ($products as $product) {
                        $discounts_sort .= $product['product_id'];
                        $id_container .= $product['product_id'];
                    }
                }
                $discounts_sort .= '_'.$discountsales_complect_discount['sales_limitation'];
                $post['discountsales_complect_discount'][$key]['discounts_sort'] = $discounts_sort;
                $id_container = mb_strcut(str_replace('.', '', hexdec(md5($id_container))),0, 6, 'utf-8');
                $post['discountsales_complect_discount'][$key]['id_container'] = $id_container;
                // = $id_container;
            }
        }

        return $post;
    }

    private function getDiscountsalesSortArray($post)
    {
        if(isset($post['discountsales_discount']) && $post['discountsales_discount']){
            foreach($post['discountsales_discount'] as $key=>$discountsales){
                $discounts_sort = $discountsales['payment_code'].'_'.$discountsales['sales_limitation'];
                $post['discountsales_discount'][$key]['discounts_sort'] = $discounts_sort;
                $id_container = mb_strcut(str_replace('.', '', hexdec(md5($discountsales['payment_code']))),0, 6, 'utf-8');
                $post['discountsales_discount'][$key]['id_container'] = $id_container;
                // = $discountsales['payment_code'];
            }
        }

        if(isset($post['discountsales_manufacturer_discount']) && $post['discountsales_manufacturer_discount']){
            foreach($post['discountsales_manufacturer_discount'] as $key=>$discountsales){
                $discounts_sort = $discountsales['manufacturer_id'].'_'.$discountsales['sales_limitation'];
                $post['discountsales_manufacturer_discount'][$key]['discounts_sort'] = $discounts_sort;
                $id_container = mb_strcut(str_replace('.', '', hexdec(md5($discountsales['manufacturer_id']))),0, 6, 'utf-8');
                $post['discountsales_manufacturer_discount'][$key]['id_container'] = $id_container;
                // = $discountsales['manufacturer_id'];
            }
        }

        if(isset($post['discountsales_category_discount']) && $post['discountsales_category_discount']){
            foreach($post['discountsales_category_discount'] as $key=>$discountsales){
                $discounts_sort = $discountsales['category_id'].'_'.$discountsales['sales_limitation'];
                $post['discountsales_category_discount'][$key]['discounts_sort'] = $discounts_sort;
                $id_container = mb_strcut(str_replace('.', '', hexdec(md5($discountsales['category_id']))),0, 6, 'utf-8');
                $post['discountsales_category_discount'][$key]['id_container'] = $id_container;
            }
        }

        return $post;
    }

    function cmpSortArrayWhisDiscounts($a, $b)
    {
        return strnatcmp($a["discounts_sort"], $b["discounts_sort"]);
    }

    //Получаем методы оплаты
    private function gepPayments()
    {
        $this->load->model('setting/extension');
        $payments_installed = $this->model_setting_extension->getInstalled('payment');
        $payments = array();
        if ($payments_installed && is_array($payments_installed)) {
            foreach ($payments_installed as $payment_installed) {
                $this->load->language('extension/payment/' . $payment_installed);
                if($this->config->get($payment_installed . '_status')){
                    $payments[] = array(
                        'title' => $this->language->get('heading_title'),
                        'code' => $payment_installed
                    );
                }
            }
        }
        return $payments;
    }

    private function getDisableData($post)
    {
        $result = array();
        if($post){
            foreach ($post as $disableData) {
                if(isset($disableData['data']) && $disableData['data']){
                    $id_disable_data = '';
                    ksort($disableData['data']);
                    foreach ($disableData['data'] as $id_data) {
                        $id_disable_data .= '_'.$id_data;
                    }
                    if(!$disableData['name']){
                        $disableData['name'] = $id_disable_data;
                    }
                    $result[$id_disable_data]['name'] = $disableData['name'];
                    $result[$id_disable_data]['data'] = $disableData['data'];
                }
            }
        }
        return $result;
    }

    //Правим точки, запятые - приводим к float
    private function getFloat($string)
    {
        $find = array('-',',',' ');
        $replace = array('.','.','');
        $result = (float)str_replace($find, $replace, $string);
        return $result;
    }

    public function getNotifications()
    {
        sleep(1);
        $this->load->language($this->route);
        $response = $this->getNotificationsCurl();
        $json = array();
        if ($response===false) {
            $json['message'] = '';
            $json['error'] = $this->language->get( 'error_notifications' );
        } else {
            $json['message'] = $response;
            $json['error'] = '';
        }
        $this->response->setOutput(json_encode($json));
    }

    protected function curl_get_contents($url)
    {
        if(function_exists('curl_version')){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            curl_close($ch);
            return $output;
        }else{
            $output['ru'] = 'Проверка версии недоступна. Включите php расширение - CURL на Вашем хостинге';
            $output['en'] = 'You can not check the version. Enable php extension - CURL on your hosting';
            $language_code = $this->config->get( 'config_admin_language' );
            if(isset($output[$language_code])){
                return $output[$language_code];
            }else{
                return $output['en'];
            }
        }
    }

    public function getNotificationsCurl()
    {
        $language_code = $this->config->get( 'config_admin_language' );
        $result = $this->curl_get_contents("http://www.".$this->this_ocext_host.".com/index.php?route=information/check_update_version&license=".HTTP_SERVER."&version_opencart=".VERSION."&version_ocext=".$this->this_version."&extension=".$this->this_extension."&language_code=$language_code");
        if (stripos($result,'<html') !== false) {
            return '';
        }
        return $result;
    }
}
