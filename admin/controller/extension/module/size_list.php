<?php
/*
 *  location: admin/controller
 */
class ControllerExtensionModuleSizeList extends Controller
{
    private $codename = 'size_list';
    private $route = 'extension/module/size_list';
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
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/user');
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
        $data['state'] = json_encode($this->getState());

        $data['pro_scripts'] = $this->extension_model->getScriptFiles();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->model_extension_pro_patch_load->view($this->route, $data));
    }

    public function get_size_list()
    {
        // GET STATE
        $data['codename'] = $this->codename;
        $data['state'] = json_encode($this->getAdminState());

        return $this->model_extension_pro_patch_load->view($this->route.'_product', $data);
    }

    public function getState()
    {
        // HEADING
        $state['heading_title'] = $this->language->get('heading_title_main');

        $lng = array(
            'text_edit', 'text_yes', 'text_no', 'text_enabled', 'text_disabled',
            'text_deleted', 'text_undefined', 'text_status', 'text_success', 'text_warning',
            'text_close', 'text_cancel', 'text_setting', 'text_debug', 'text_no', 'text_yes',
            'text_fee', 'text_access_code', 'text_no_results', 'text_loading',

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

        $state['get_items'] = $this->model_extension_pro_patch_url->ajax("{$this->route}/get_items");
        $state['get_item'] = $this->model_extension_pro_patch_url->ajax("{$this->route}/get_item");
        $state['save_item'] = $this->model_extension_pro_patch_url->ajax("{$this->route}/save_item");
        $state['remove_item'] = $this->model_extension_pro_patch_url->ajax("{$this->route}/remove_item");

        // SETTING
        $state['setting'] = $this->setting;
        if (isset($state['setting']['status']) && $state['setting']['status']) {
            $state['setting']['status'] = true;
        } else { $state['setting']['status'] = false; }

        // SET STATE
        return $state;
    }

    public function getAdminState()
    {
        // VARIABLE
        $state['id'] = $this->codename;
        $state['route'] = $this->route;
        $state['version'] = $this->extension['version'];
        $state['token'] = $this->model_extension_pro_patch_user->getUrlToken();

        $state['get_size_shit'] = $this->model_extension_pro_patch_url->ajax("{$this->route}/get_size_shit");
        $state['update_product_data'] = $this->model_extension_pro_patch_url->ajax("{$this->route}/update_product_data");

        $state['product_id'] = (isset($this->request->get['product_id'])) ? $this->request->get['product_id'] : null;
        $state['size_shit'] = $this->extension_model->prepareForTree( $this->extension_model->getAllSizeList(null, $state['product_id']) );
        $state['product_item'] = $this->extension_model->getImageForProduct($state['product_id']);

        // SET STATE
        return $state;
    }

    public function get_items()
    {
        $json['items'] = $this->extension_model->getItems();

        $this->response->setOutput(json_encode($json));
    }

    public function get_item()
    {
        $parsed = $this->model_extension_pro_patch_json->parseJson(file_get_contents('php://input'));

        if (isset($parsed['image_id'])) {

            if (empty($parsed['image_id'])) { $parsed['image_id'] = null; }
            $json['item'] = $this->extension_model->getItem($parsed['image_id'], true);

        } else {
            $json['error'][] = $this->language->get('error_corrupted_request');
        }

        $this->response->setOutput(json_encode($json));
    }

    public function save_item()
    {
        $json = $this->model_extension_pro_patch_permission->validateRoute($this->route);

        if (!isset($json['error'])) {
            $parsed = $this->model_extension_pro_patch_json->parseJson(file_get_contents('php://input'));

            if (isset($parsed['item'])) {
                $result = $this->extension_model->saveItem($parsed['item']);
                $json = array_merge_recursive($json, $result);
            } else {
                $json['error'][] = $this->language->get('error_corrupted_request');
            }
        }

        $this->response->setOutput(json_encode($json));
    }

    public function remove_item()
    {
        $json = $this->model_extension_pro_patch_permission->validateRoute($this->route);

        if (!isset($json['error'])) {
            $parsed = $this->model_extension_pro_patch_json->parseJson(file_get_contents('php://input'));

            if (isset($parsed['image_id'])) {
                $result = $this->extension_model->removeItem($parsed['image_id']);
            } else {
                $json['error'][] = $this->language->get('error_corrupted_request');
            }
        }

        $this->response->setOutput(json_encode($json));
    }

    public function get_size_shit()
    {
        $parsed = $this->model_extension_pro_patch_json->parseJson(file_get_contents('php://input'));

        $q = (isset($parsed['q'])) ? $parsed['q'] : null;
        $m = $this->extension_model->getAllSizeList($q);
        $json['manufacturers'] = $this->extension_model->prepareForTree($m);

        $this->response->setOutput(json_encode($json));
    }

    public function update_product_data()
    {
        $parsed = $this->model_extension_pro_patch_json->parseJson(file_get_contents('php://input'));

        if (isset($parsed['product_id']) && $parsed['product_id']) {
            $id = isset($parsed['selected']) ? $parsed['selected'] : false;
            $this->extension_model->saveImageForProduct($parsed['product_id'], $id);

            if (isset($parsed['selected'])) {
                $json['success'][] = 'Табличка обновлена';
            } else {
                $json['success'][] = 'Табличка очищенна';
            }

        } else {
            $json['error'][] = $this->language->get('error_corrupted_request');
        }

        $this->response->setOutput(json_encode($json));
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

                if (isset($parsed['status']) && $parsed['status']) {
                    $this->uninstallEvents();
                    $this->installEvents();
                } else {
                    $this->uninstallEvents();
                }

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
}