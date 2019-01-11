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

        $this->load->language('catalog/product');
        $this->load->language($this->route);

        $this->load->model($this->route);
        $this->load->model('extension/pro_patch/url');
        $this->load->model('extension/pro_patch/load');
        $this->load->model('extension/pro_patch/user');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/setting');
        $this->load->model('extension/pro_patch/permission');
        $this->load->model('extension/pro_patch/modification');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);

        $this->pro_hamster = (file_exists(DIR_SYSTEM.'library/pro_hamster/extension/pro_hamster.json'));
        $this->pro_patch = (file_exists(DIR_SYSTEM.'library/pro_hamster/extension/pro_patch.json'));
        $this->extension = json_decode(file_get_contents(DIR_SYSTEM."library/pro_hamster/extension/{$this->codename}.json"), true);
        $this->d_event_manager = (file_exists(DIR_SYSTEM.'library/d_shopunity/extension/d_event_manager.json'));

        $this->extension_model = $this->{'model_'.str_replace("/", "_", $this->route)};
    }

    public function index()
    {
        // HEADING
        $this->document->setTitle($this->language->get('heading_title_main'));

        // STATE
        $data['codename'] = $this->codename;
        $data['state'] = json_encode($this->getAdminState());

        $data['pro_scripts'] = $this->extension_model->getScriptFiles(true);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->model_extension_pro_patch_load->view($this->route, $data));
    }

    public function getAdminState()
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

    public function getState($data)
    {
        // STATE
        $state = array(
            'options' => false,
            'option_values' => false,
            'active_columns' => false,
            'combinations' => false,
            'setting' => false,
        );

        $lng = array(
            'text_edit', 'text_yes', 'text_no', 'text_enabled', 'text_disabled',
            'text_deleted', 'text_undefined', 'text_status', 'text_success', 'text_warning',
            'text_close', 'text_cancel', 'text_setting', 'text_debug', 'text_fee',
            'text_access_code', 'text_subtract', 'text_price_prefix', 'text_special_prefix',
            'text_reward_prefix', 'text_weight_prefix', 'text_import_default_options',
            'tab_setting', 'text_product_code', 'text_special_price', 'text_column_toggle',
            'text_quick_filter', 'text_model', 'text_quantity', 'text_price', 'text_special',
            'text_reward', 'text_weight', 'text_filter_placeholder', 'text_no_combinations',
            'text_no_options', 'text_nothing_to_configure',

            'entry_option',

            'button_save_and_stay', 'button_save', 'button_cancel', 'button_edit', 'button_delete',
            'button_generate_comb', 'button_add_option', 'button_delete_option',

        );
        for ($i = 0; $i < sizeof($lng); $i++) { $state[$lng[$i]] = $this->language->get($lng[$i]); }

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

        $state['product_id'] = (int)$data['product_id'];

        $oav = $this->extension_model->getOptionsAndValues((int)$data['product_id']);
        $state['options'] = $oav['product_options'];
        $state['option_values'] = $oav['option_values'];

        $state['active_columns'] = $this->extension_model->activeColumnsFiller($state);
        $state['combinations'] = $this->extension_model->getCombinations($state);
        $state['combinations_data'] = $this->extension_model->getCombinationsData($state);
        $state['default_active_columns'] = $this->extension_model->getDefaultActiveColumns();

        $state['full_colspan'] = 1;
        if (is_array($state['active_columns'])) {
            foreach ($state['active_columns'] as $ac) { if ($ac['active']) { $state['full_colspan']++; } }
        }

        $state['setting'] = array(
            'subtract_stock'        => true,
            'price_prefix'          => '=',
            'special_price_prefix'  => '=',
            'reward_point_prefix'   => '=',
            'weight_prefix'         => '='
        );

        // SET STATE
        return $state;
    }

    public function get_product_options($data)
    {
        // GET STATE
        $data['state'] = json_encode($this->getState($data));

        return $this->model_extension_pro_patch_load->view('extension/super_offers/product/options', $data);
    }

    public function get_product_options_green_button()
    {
        $data = array();
        $data['button_save_and_stay'] = $this->language->get('button_save_and_stay');
        $data['success_product_saved'] = $this->language->get('success_product_saved');
        return $this->model_extension_pro_patch_load->view('extension/super_offers/product/green_button', $data);
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

                if (isset($parsed['status']) && $parsed['status']) {
                    $this->uninstallEvents();
                    $this->installEvents();
                    $this->model_extension_pro_patch_modification->modificationHandler($this->codename, false);
                    $this->model_extension_pro_patch_modification->modificationHandler($this->codename, true);
                } else {
                    $this->uninstallEvents();
                    $this->model_extension_pro_patch_modification->modificationHandler($this->codename, false);
                }

                $this->model_extension_pro_patch_setting->editSetting($this->type, $this->codename, $post, $this->store_id);
                $json['success'][] = $this->language->get('success_setting_saved');
            }
        }

        $this->response->setOutput(json_encode($json));
    }

    public function reinstallEvents()
    {
        $json = $this->model_extension_pro_patch_permission->validateRoute($this->route);
        if (!isset($json['error'])) {
            $this->uninstallEvents();
            $this->installEvents();
            $json['success'][] = $this->language->get('success_reinstall_events');
        }
        $this->response->setOutput(json_encode($json));
    }

    private function installEvents()
    {
        if ($this->d_event_manager) {
            $this->load->model('extension/module/d_event_manager');
            if (isset($this->setting['events'])
                && is_array($this->setting['events'])) {
                foreach ($this->setting['events'] as $trigger => $action) {
                    $this->model_extension_module_d_event_manager->addEvent($this->codename, $trigger, $action);
                }
            }
        }
    }

    private function uninstallEvents()
    {
        if ($this->d_event_manager) {
            $this->load->model('extension/module/d_event_manager');
            $this->model_extension_module_d_event_manager->deleteEvent($this->codename);
        }
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