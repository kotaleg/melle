<?php

class ControllerExtensionModuleDBlogModuleRelatedPost extends Controller {
    private $id = 'd_blog_module_related_post';
    private $route = 'extension/module/d_blog_module_related_post';
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

        $this->mbooth = $this->model_extension_module_d_blog_module->getMboothFile($this->id, $this->sub_versions);
        $this->config_file = $this->model_extension_module_d_blog_module->getConfigFile($this->id, $this->sub_versions);
        $this->setting = $this->model_extension_module_d_blog_module->getConfigData($this->id, $this->id.'_setting', $this->config->get('config_store_id'),$this->config_file);

        $config_file = $this->model_extension_module_d_blog_module->getConfigFile('d_blog_module', $this->sub_versions);
        $d_blog_module_setting = $this->model_extension_module_d_blog_module->getConfigData('d_blog_module', 'd_blog_module_setting', $this->config->get('config_store_id'),$config_file);

        $this->setting = $this->setting + $d_blog_module_setting;
    }

    public function index() {

        // if (file_exists(DIR_TEMPLATE . $this->theme . '/stylesheet/d_blog_module/d_blog_module.css')) {
        //     $this->document->addStyle('catalog/view/theme/'.$this->theme.'/stylesheet/d_blog_module/d_blog_module.css');
        // } else {
        //     $this->document->addStyle('catalog/view/theme/default/stylesheet/d_blog_module/d_blog_module.css');
        // }

        $this->document->addStyle('catalog/view/theme/default/stylesheet/d_blog_module/theme/'.$this->setting['theme'].'.css');


        if (isset($this->request->get['post_id'])) {
            $post_id = (int) $this->request->get['post_id'];
        } else {
            $post_id = null;
        }


        if($post_id){

            $posts = $this->model_extension_module_d_blog_module_related_post->getPostRelateds($post_id);
            $data['posts'] = array();
            foreach ($posts as $post) {
                $data['posts'][] = $this->load->controller('extension/d_blog_module/post/thumb', $post['post_id']);
            }
            $data['setting'] = $this->setting;

            $this->load->language($this->route);
            $data['heading_title'] = $this->language->get('heading_title');

            $data['entry_qty'] = $this->language->get('entry_qty');
            $data['entry_author'] = $this->language->get('entry_author');
            $data['entry_reply_to'] = $this->language->get('entry_reply_to');
            $data['text_cancel'] = $this->language->get('text_cancel');

            if(empty($data['posts'])) {
                return;
            } else {
                return $this->load->view('extension/module/d_blog_module_related_post', $data);
            }
        }

    }

}
