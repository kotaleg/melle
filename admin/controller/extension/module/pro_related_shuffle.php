<?php
/*
 *  location: admin/controller
 */
class ControllerExtensionModuleProRelatedShuffle extends Controller
{
    private $codename = 'pro_related_shuffle';
    private $route = 'extension/module/pro_related_shuffle';
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

        // TEXT
        $state['text_edit'] = $this->language->get('text_edit');
        $state['text_yes'] = $this->language->get('text_yes');
        $state['text_no'] = $this->language->get('text_no');
        $state['text_enabled'] = $this->language->get('text_enabled');
        $state['text_disabled'] = $this->language->get('text_disabled');
        $state['text_deleted'] = $this->language->get('text_deleted');
        $state['text_undefined'] = $this->language->get('text_undefined');
        $state['text_status'] = $this->language->get('text_status');
        $state['text_close'] = $this->language->get('text_close');
        $state['text_cancel'] = $this->language->get('text_cancel');
        $state['text_reshuffle_all'] = $this->language->get('text_reshuffle_all');
        $state['text_product_number'] = $this->language->get('text_product_number');
        $state['text_warning'] = $this->language->get('text_warning');
        $state['text_are_you_sure'] = $this->language->get('text_are_you_sure');
        $state['text_proceed_count'] = $this->language->get('text_proceed_count');
        $state['text_most_close_only'] = $this->language->get('text_most_close_only');
        $state['text_preparing'] = $this->language->get('text_preparing');
        $state['text_relate_new_product'] = $this->language->get('text_relate_new_product');

        // BUTTON
        $state['button_save_and_stay'] = $this->language->get('button_save_and_stay');
        $state['button_save'] = $this->language->get('button_save');
        $state['button_cancel'] = $this->language->get('button_cancel');
        $state['button_edit'] = $this->language->get('button_edit');
        $state['button_delete'] = $this->language->get('button_delete');

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

        $state['shuffle_all_products'] = $this->model_extension_pro_patch_url->ajax("{$this->route}/shuffle_all_products");
        $state['cancel_shuffle'] = $this->model_extension_pro_patch_url->ajax("{$this->route}/cancel_shuffle");

        // SETTING
        $state['setting'] = $this->setting;
        if (isset($state['setting']['status']) && $state['setting']['status']) {
            $state['setting']['status'] = true;
        } else { $state['setting']['status'] = false; }

        if (isset($state['setting']['most_close_only']) && $state['setting']['most_close_only']) {
            $state['setting']['most_close_only'] = true;
        } else { $state['setting']['most_close_only'] = false; }

        if (isset($state['setting']['relate_new_product']) && $state['setting']['relate_new_product']) {
            $state['setting']['relate_new_product'] = true;
        } else { $state['setting']['relate_new_product'] = false; }

        // SET STATE
        return $state;
    }

    public function shuffle_all_products()
    {
        $json = $this->model_extension_pro_patch_permission->validateRoute($this->route);
        $json['continue'] = false;
        $json['loading_progress'] = 0;
        $json['loading_message'] = '';

        if (!isset($json['error'])) {
            $json['continue'] = $this->extension_model->shuffleAllProducts();

            $result = $this->extension_model->getLoadingProgress();
            $json['loading_progress'] = $result['progress'];
            $json['loading_message'] = $result['message'];

            if (!$json['continue']) {
                $json['success'][] = $this->language->get('success_products_shuffled');
            }
        }

        $this->response->setOutput(json_encode($json));
    }

    public function cancel_shuffle()
    {
        $json = $this->model_extension_pro_patch_permission->validateRoute($this->route);

        if (!isset($json['error'])) {
            $this->extension_model->cancelShuffle();
            $json['message'][] = $this->language->get('success_cancel_in_progress');
        }

        $this->response->setOutput(json_encode($json));
    }

    public function save()
    {
        $json = $this->model_extension_pro_patch_permission->validateRoute($this->route);
        if (!isset($json['error'])) {
            $parsed = $this->extension_model->parseJson(file_get_contents('php://input'));
            if ($parsed) {
                $post = array();

                foreach ($parsed as $k => $v) {
                    $post["{$this->codename}_{$k}"] = $v;
                }

                if (isset($post["{$this->codename}_product_number"]) && isset($post["{$this->codename}_proceed_count"])) {
                    $post["{$this->codename}_product_number"] = intval($post["{$this->codename}_product_number"]);
                    $post["{$this->codename}_proceed_count"] = intval($post["{$this->codename}_proceed_count"]);

                    if ($post["{$this->codename}_product_number"] > 0) {

                        if ($post["{$this->codename}_proceed_count"] > 0) {

                            // SAVE
                            $this->model_extension_pro_patch_setting->editSetting($this->type, $this->codename, $post, $this->store_id);
                            $json['success'][] = $this->language->get('success_setting_saved');

                            // MODS
                            $this->model_extension_pro_patch_modification->modificationHandler($this->codename, false);
                            if ($parsed['relate_new_product']) {
                                $this->model_extension_pro_patch_modification->modificationHandler($this->codename, true);
                            }

                        } else {
                            $json['error'][] = $this->language->get('error_not_valid_proceed_number');
                        }

                    } else {
                        $json['error'][] = $this->language->get('error_not_valid_product_number');
                    }

                } else {
                    $json['error'][] = $this->language->get('error_corrupted_request');
                }
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