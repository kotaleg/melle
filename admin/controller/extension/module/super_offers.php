<?php
/*
 *  location: admin/controller
 */
class ControllerExtensionModuleSuperOffers extends Controller
{
    private $codename = 'super_offers';
    private $route = 'extension/module/super_offers';
    private $type = 'module';

    private $store_id = 0;
    private $error = array();
    private $setting = array();

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language($this->route);

        $this->load->model($this->route);
        $this->load->model('extension/pro_patch/url');
        $this->load->model('extension/pro_patch/load');
        $this->load->model('extension/pro_patch/user');
        $this->load->model('extension/pro_patch/setting');
        $this->load->model('extension/pro_patch/permission');
        $this->load->model('extension/pro_patch/modification');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);

        $this->pro_hamster = (file_exists(DIR_SYSTEM.'library/pro_hamster/extension/pro_hamster.json'));
        $this->pro_patch = (file_exists(DIR_SYSTEM.'library/pro_hamster/extension/pro_patch.json'));
        $this->extension = json_decode(file_get_contents(DIR_SYSTEM."library/pro_hamster/extension/{$this->codename}.json"), true);

        $this->extension_model = $this->{'model_'.str_replace("/", "_", $this->route)};
    }

    public function index()
    {
        // HEADING
        $this->document->setTitle($this->language->get('heading_title_main'));

        // STATE
        $data['codename'] = $this->codename;
        $data['state'] = $this->getState();

        $data['pro_scripts'] = $this->extension_model->getScriptFiles();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->model_extension_pro_patch_load->view($this->route, $data));
    }

    public function getState()
    {
        // HEADING
        $state['heading_title'] = $this->language->get('heading_title_main');

        $lng = array(
            'text_edit', 'text_yes', 'text_no', 'text_enabled', 'text_disabled',
            'text_deleted', 'text_undefined', 'text_status', 'text_success', 'text_warning',
            'text_close', 'text_cancel', 'text_setting', 'text_debug', 'text_no', 'text_yes',
            'text_fee', 'text_access_code',

            'button_save_and_stay', 'button_save', 'button_cancel', 'button_edit', 'button_delete',

        );
        for ($i = 0; $i < sizeof($lng); $i++) { $state[$lng[$i]] = $this->language->get($lng[$i]); }

        // BREADCRUMB
        $state['breadcrumbs'] = array();
        $state['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->model_extension_pro_patch_url->ajax('common/dashboard'),
        );

        $state['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->model_extension_pro_patch_url->getExtensionAjax('module'),
        );

        $state['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title_main'),
            'href'      => $this->model_extension_pro_patch_url->ajax($this->route),
        );

        // VARIABLE
        $state['id'] = $this->codename;
        $state['route'] = $this->route;
        $state['version'] = $this->extension['version'];
        $state['token'] = $this->model_extension_pro_patch_user->getUrlToken();

        // ACTION
        $state['module_link'] = $this->model_extension_pro_patch_url->ajax($this->route);
        $state['cancel'] = $this->model_extension_pro_patch_url->getExtensionAjax('module');
        $state['get_cancel'] = $this->model_extension_pro_patch_url->getExtensionAjax('module');
        $state['save'] = $this->model_extension_pro_patch_url->ajax("{$this->route}/save");

        // SETTING
        $state['setting'] = $this->setting;
        if (isset($state['setting']['status']) && $state['setting']['status']) {
            $state['setting']['status'] = true;
        } else { $state['setting']['status'] = false; }

        // SET STATE
        return $state;
    }

    public function save()
    {
        $json = $this->model_extension_pro_patch_permission->validateRoute($this->route);

        if (!isset($json['error'])) {
            $parsed = $this->model_extension_pro_patch_json->parseJson(file_get_contents('php://input'));
            if ($parsed) {
                $post = array();

                foreach ($parsed as $k => $v) {
                    $post["{$this->codename}_{$k}"] = $v;
                }

                $this->model_extension_pro_patch_setting->editSetting($this->type, $this->codename, $post, $this->store_id);
                $json['success'][] = $this->language->get('success_setting_saved');
            }
        }

        $this->response->setOutput(json_encode($json));
    }

    public function install()
    {
        $this->model_extension_pro_patch_permission->addPermission($this->codename, true);
        $this->extension_model->createTables();
    }

    public function uninstall()
    {
        $this->extension_model->dropTables();
        $this->model_extension_pro_patch_modification->modificationHandler($this->codename, false);
    }
}