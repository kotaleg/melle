<?php
/**
* location: admin/controller
*/
class ControllerExtensionModuleDBlogModuleRelatPostToProd extends Controller {
    private $codename = 'd_blog_module_relat_post_to_prod';
    private $route = 'extension/module/d_blog_module_relat_post_to_prod';
    private $sub_versions = array('lite', 'light', 'free');
    private $config_file = '';
    private $prefix = '';
    private $store_id = 0;
    private $error = array(); 

    public function __construct($registry) {
        parent::__construct($registry);
        $this->load->model('extension/d_opencart_patch/url');
        $this->load->model('extension/d_opencart_patch/user');
        $this->load->model('extension/d_opencart_patch/load');

        $this->d_shopunity = (file_exists(DIR_SYSTEM.'library/d_shopunity/extension/d_shopunity.json'));
        $this->extension = json_decode(file_get_contents(DIR_SYSTEM.'library/d_shopunity/extension/d_blog_module_pack.json'), true);
        $this->d_admin_style = (file_exists(DIR_SYSTEM.'library/d_shopunity/extension/d_admin_style.json'));
        
        if (isset($this->request->get['store_id'])) { 
            $this->store_id = $this->request->get['store_id']; 
        }
    }

    public function index() {

        if($this->d_shopunity){
            $this->load->model('extension/d_shopunity/mbooth');
            $this->model_extension_d_shopunity_mbooth->validateDependencies('d_blog_module_pack');
        }

        if($this->d_twig_manager){
            $this->load->model('extension/module/d_twig_manager');
            $this->model_extension_module_d_twig_manager->installCompatibility();
        }

        if ($this->d_admin_style){
            $this->load->model('extension/d_admin_style/style');
            $this->model_extension_d_admin_style_style->getStyles('light');
        }
        //dependencies
        $this->load->language($this->route);
        $this->load->model($this->route);
        $this->load->model('extension/d_shopunity/setting');
        $this->load->model('setting/setting');
        $this->load->model('extension/d_blog_module/category');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $new_post = array();
            foreach ($this->request->post as $k => $v) {
                $new_post['module_'.$k] = $v;
            }
            $this->model_setting_setting->editSetting($this->codename, $this->request->post, $this->store_id);
            $this->model_setting_setting->editSetting('module_'.$this->codename, $new_post, $this->store_id);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->model_extension_d_opencart_patch_url->getExtensionLink('module'));
        }

        $this->document->addStyle('view/stylesheet/shopunity/bootstrap.css');
        $this->document->addScript('view/javascript/d_bootstrap_switch/js/bootstrap-switch.min.js');
        $this->document->addStyle('view/javascript/d_bootstrap_switch/css/bootstrap-switch.css');


        $url_params = array();
        $url = '';

        if(isset($this->response->get['store_id'])){
            $url_params['store_id'] = $this->store_id;
        }

        if(isset($this->response->get['config'])){
            $url_params['config'] = $this->response->get['config'];
        }

        $url = ((!empty($url_params)) ? '&' : '' ) . http_build_query($url_params);

        $this->document->setTitle($this->language->get('heading_title_main'));
        $data['heading_title'] = $this->language->get('heading_title_main');

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');

        $data['entry_status'] = $this->language->get('entry_status');
        $data['help_where_to_position'] = $this->language->get('help_where_to_position');
        // Button
        $data['button_save'] = $this->language->get('button_save');
        $data['button_save_and_stay'] = $this->language->get('button_save_and_stay');
        $data['button_cancel'] = $this->language->get('button_cancel');

        // Variable
        $data['codename'] = $this->codename;
        $data['route'] = $this->route;
        $data['store_id'] = $this->store_id;
        $data['stores'] = $this->model_extension_d_shopunity_setting->getStores();
        $data['config'] = $this->config_file;
        $data['support_url'] = $this->extension['support']['url'];
        $data['version'] = $this->extension['version'];
        $data['token'] =  $this->model_extension_d_opencart_patch_user->getToken();

        //support
        $data['tab_support'] = $this->language->get('tab_support');
        $data['text_support'] = $this->language->get('text_support');
        $data['entry_support'] = $this->language->get('entry_support');
        $data['button_support'] = $this->language->get('button_support');
        $data['text_powered_by'] = $this->language->get('text_powered_by');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        // Breadcrumbs
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->model_extension_d_opencart_patch_url->link('common/dashboard')
            );
    
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->model_extension_d_opencart_patch_url->getExtensionLink('module')
            );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->model_extension_d_opencart_patch_url->link($this->route, $url)
            );

        //action
        $data['module_link'] = $this->model_extension_d_opencart_patch_url->link($this->route);
        $data['action'] = $this->model_extension_d_opencart_patch_url->link($this->route, $url);
        $data['cancel'] = $this->model_extension_d_opencart_patch_url->getExtensionLink('module');


        if (isset($this->request->post[$this->codename.'_status'])) {
            $data[$this->codename.'_status'] = $this->request->post[$this->codename.'_status'];
        } else {
            $data[$this->codename.'_status'] = $this->config->get($this->codename.'_status');
        }

                //get setting
        $data['setting'] = $this->model_extension_module_d_blog_module_relat_post_to_prod->getConfigData($this->codename, $this->codename.'_setting', $this->store_id, $this->config_file);

    
        
        //get config 
        $data['config_files'] = $this->model_extension_module_d_blog_module_relat_post_to_prod->getConfigFiles($this->codename);


        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->model_extension_d_opencart_patch_load->view($this->route, $data));
    }
    private function validate($permission = 'modify') {

        if (isset($this->request->post['config'])) {
            return false;
        }

        $this->language->load($this->route);
        
        if (!$this->user->hasPermission($permission, $this->route)) {
            $this->error['warning'] = $this->language->get('error_permission');
            return false;
        }

        return true;
    }
    public function install() {
        if($this->d_shopunity){
            $this->load->model('extension/d_shopunity/mbooth');
            $this->model_extension_d_shopunity_mbooth->installDependencies('d_blog_module_pack');
        }
    }
    public function uninstall(){
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting($this->codename);
        $this->model_setting_setting->deleteSetting('module_'.$this->codename);
    }
}