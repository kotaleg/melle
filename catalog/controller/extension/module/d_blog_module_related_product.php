<?php

class ControllerExtensionModuleDBlogModuleRelatedProduct extends Controller {
    private $id = 'd_blog_module_related_product';
    private $route = 'extension/module/d_blog_module_related_product';
    private $sub_versions = array('lite', 'light', 'free');
    private $mbooth = '';
    private $prefix = '';
    private $config_file = '';
    private $error = array(); 
    private $debug = false;
    private $setting = array();

    public function __construct($registry) {
        parent::__construct($registry);
        if(!isset($this->user)){
            if(VERSION >= '2.2.0.0'){
                $this->user = new Cart\User($registry);
            }else{
                $this->user = new User($registry);
            }
        }
        
        $this->load->model($this->route);
        $this->load->model('extension/module/d_blog_module');

        $this->config_file = $this->model_extension_module_d_blog_module->getConfigFile($this->id, $this->sub_versions);
        $this->setting = $this->model_extension_module_d_blog_module->getConfigData($this->id, $this->id.'_setting', $this->config->get('config_store_id'),$this->config_file);

        $config_file = $this->model_extension_module_d_blog_module->getConfigFile('d_blog_module', $this->sub_versions);
        $d_blog_module_setting = $this->model_extension_module_d_blog_module->getConfigData('d_blog_module', 'd_blog_module_setting', $this->config->get('config_store_id'),$config_file);

        $this->setting = $this->setting + $d_blog_module_setting;
    }

    public function index() {

        if (isset($this->request->get['post_id'])) {
            $post_id = (int) $this->request->get['post_id'];
        } else {
            $post_id = null;
        }

        
        if($post_id){
            $this->load->model('catalog/product');
            $products = $this->model_extension_module_d_blog_module_related_product->getPostProducts($post_id, $this->setting['limit']);
            $data['products'] = array();
            foreach ($products as $product) {
                $result = $this->model_catalog_product->getProduct($product['product_id']);

                if(VERSION >= '3.0.0.0'){
                    $width = $this->config->get('theme_'. $this->config->get('config_theme') . '_image_product_width');
                    $height = $this->config->get('theme_'. $this->config->get('config_theme') . '_image_product_height');
                }elseif(VERSION >= '2.2.0.0'){
                    $width = $this->config->get($this->config->get('config_theme') . '_image_product_width');
                    $height = $this->config->get($this->config->get('config_theme') . '_image_product_height');
                }else{
                    $width = $this->config->get('config_image_product_width');
                    $height = $this->config->get('config_image_product_height');
                }
                if ($result['image']) {
                    $image = $this->model_tool_image->resize($result['image'], $width, $height);
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', $width, $height);
                }

                if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
                    $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                } else {
                    $price = false;
                }
                if ((float)$result['special']) {
                    $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                } else {
                    $special = false;
                }

                if ($this->config->get('config_tax')) {
                    $tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
                } else {
                    $tax = false;
                }

                if ($this->config->get('config_review_status')) {
                    $rating = (int)$result['rating'];
                } else {
                    $rating = false;
                }

                if(VERSION >= '3.0.0.0'){
                    $description_length = $this->config->get('theme_'.$this->config->get('config_theme') . '_product_description_length');
                }elseif(VERSION >= '2.2.0.0'){
                    $description_length = $this->config->get($this->config->get('config_theme') . '_product_description_length');
                }else{
                    $description_length = $this->config->get('config_product_description_length');
                }
                
                $data['products'][] = array(
                    'product_id'  => $result['product_id'],
                    'thumb'       => $image,
                    'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
                    'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $description_length ) . '..',
                    'price'       => $price,
                    'special'     => $special,
                    'tax'         => $tax,
                    'text_tax'    => $this->language->get('text_tax'),
                    'button_cart'    => $this->language->get('button_cart'),
                    'button_wishlist'    => $this->language->get('button_wishlist'),
                    'button_compare'    => $this->language->get('button_compare'),
                    'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
                    'rating'      => $rating,
                    'href'        =>  strip_tags(html_entity_decode($this->url->link('product/product','product_id=' . $result['product_id']), ENT_QUOTES, 'UTF-8'))
                    );
            }

            $data['setting'] = $this->setting;

            $this->load->language($this->route);
            $data['heading_title'] = $this->language->get('heading_title');

            $data['entry_qty'] = $this->language->get('entry_qty');
            $data['entry_author'] = $this->language->get('entry_author');
            $data['entry_reply_to'] = $this->language->get('entry_reply_to');
            $data['text_cancel'] = $this->language->get('text_cancel');

            if(empty($data['products'])) {
                return;
            } else {
                return $this->load->view('extension/module/d_blog_module_related_product', $data);
            }

            
        }
    }
}
