<?php

class ControllerExtensionModuleDBlogModuleCategory extends Controller {
    private $id = 'd_blog_module_category';
    private $route = 'extension/module/d_blog_module_category';
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
        $this->load->model('extension/d_opencart_patch/load');
        $this->load->model('extension/d_blog_module/category');
        $this->load->model('extension/module/d_blog_module');

        $this->config_file = $this->model_extension_module_d_blog_module->getConfigFile($this->id, $this->sub_versions);
        $this->setting = $this->model_extension_module_d_blog_module->getConfigData($this->id, $this->id.'_setting', $this->config->get('config_store_id'),$this->config_file);

        $config_file = $this->model_extension_module_d_blog_module->getConfigFile('d_blog_module', $this->sub_versions);
        $d_blog_module_setting = $this->model_extension_module_d_blog_module->getConfigData('d_blog_module', 'd_blog_module_setting', $this->config->get('config_store_id'),$config_file);

        $this->setting = $this->setting + $d_blog_module_setting;
    }

    public function index() {

        if (file_exists(DIR_TEMPLATE . $this->theme . '/stylesheet/d_blog_module/d_blog_module.css')) {
            $this->document->addStyle('catalog/view/theme/'.$this->theme.'/stylesheet/d_blog_module/d_blog_module.css');
        } else {
            $this->document->addStyle('catalog/view/theme/default/stylesheet/d_blog_module/d_blog_module.css');
        }

        $this->document->addStyle('catalog/view/theme/default/stylesheet/d_blog_module/theme/'.$this->setting['theme'].'.css');

        if (isset($this->request->get['category_id'])) {
            $category_id = $this->request->get['category_id'];
        } else {
            $category_id = $this->setting['category']['main_category_id'];
        }

        if (isset($this->request->get['post_id'])) {
            $post_id = (int) $this->request->get['post_id'];
        } else {
            $post_id = null;
        }


        if($post_id){
            $categories = $this->model_extension_d_blog_module_category->getCategoryByPostId($post_id);
            if(isset($categories[0])){
                $category_id = $categories[0]['category_id'];
            }
        }


        $data['category_id'] = $category_id;
        $categories = $this->model_extension_d_blog_module_category->getCategories( $this->setting['category']['main_category_id']);
        $data['categories'] = array();

        $category_info = $this->model_extension_d_blog_module_category->getCategory($this->setting['category']['main_category_id']);
        if(isset($category_info['title'])){
            $data['heading_title'] = $category_info['title'];
        }else{
            $data['heading_title'] = false;
        }


        foreach ($categories as $category) {
            $children_data = array();

            $selected = 0;
            $children = $this->model_extension_d_blog_module_category->getCategories($category['category_id']);

            foreach($children as $child) {
                $selected_child = ($child['category_id'] == $data['category_id']) ? 1 : 0;
                if($selected_child){
                    $selected = 1;
                }
                $filter_data = array('filter_category_id' => $child['category_id'], 'filter_sub_category' => true);

                $children_data[] = array(
                    'category_id' => $child['category_id'],
                    'selected' => $selected_child,
                    'title' => $child['title'],
                    'href' => $this->url->link('extension/d_blog_module/category', 'category_id=' . $child['category_id'], 'SSL')
                    );
            }

            if(!$selected){
                $selected = ($category['category_id'] == $data['category_id']) ? 1 : 0;
            }

            $filter_data = array(
                'filter_category_id'  => $category['category_id'],
                'filter_sub_category' => true
                );

            $data['categories'][] = array(
                'category_id' => $category['category_id'],
                'selected'    => $selected,
                'title'       => $category['title'],
                'children'    => $children_data,
                'href'        => $this->url->link('extension/d_blog_module/category', 'category_id=' . $category['category_id'], 'SSL')
                );
        }

        $data['setting'] = $this->setting;

        $data['entry_qty'] = $this->language->get('entry_qty');
        $data['entry_author'] = $this->language->get('entry_author');
        $data['entry_reply_to'] = $this->language->get('entry_reply_to');
        $data['text_cancel'] = $this->language->get('text_cancel');

        if(empty($data['categories'])) {
            return;
        } else {
            return $this->model_extension_d_opencart_patch_load->view('extension/module/d_blog_module_category', $data);
        }
    }

}
