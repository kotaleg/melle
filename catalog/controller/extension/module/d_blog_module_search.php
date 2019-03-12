<?php

class ControllerExtensionModuleDBlogModuleSearch extends Controller {
    private $id = 'd_blog_module_search';
    private $route = 'extension/module/d_blog_module_search';
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

        $this->load->model('extension/d_blog_module/post');
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

       $this->load->language($this->route);
        $module_info = $this->config->get($this->id);
        if(isset($module_info['limit'])){
            $limit = $module_info['limit'];
        }else{
            $limit = 5;
        }
        if (isset($this->request->get['search'])) {
            $search = $this->request->get['search'];
        } 
        else {
            $search = '';
        }
        $filter_data = array('filter_name' => $search, 'filter_description' => $search, 'limit' => $limit,'start' => 0);

        $posts = $this->model_extension_d_blog_module_post->getPosts($filter_data);

        $this->load->language($this->route);
        $data['posts'] = array();
        if($posts){
            foreach ($posts as $post) {
               $data['posts'][] = $this->load->controller('extension/d_blog_module/post/thumb', $post['post_id']);
            }
            $data['setting'] = $this->setting;
            
            $data['heading_title'] = $this->language->get('heading_title');
            $data['text_write'] = $this->language->get('text_write');
            $data['text_reply'] = $this->language->get('text_reply');
            $data['text_review'] = $this->language->get('text_review');
            $data['text_views'] = $this->language->get('text_views');
            $data['text_read_more'] = $this->language->get('text_read_more');

            $data['entry_qty'] = $this->language->get('entry_qty');
            $data['entry_author'] = $this->language->get('entry_author');
            $data['entry_reply_to'] = $this->language->get('entry_reply_to');
            $data['text_cancel'] = $this->language->get('text_cancel');
       
        }else{
            $data['text_empty'] = $this->language->get('text_empty');
        }
        
        $status = $this->config->get($this->id.'_status');

        if($status)
        {

            if(empty($data['posts'])) {
                return;
            } else {
                return $this->load->view('extension/module/d_blog_module_search', $data);
            }
           
        }
    }
}