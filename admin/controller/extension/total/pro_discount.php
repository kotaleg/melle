<?php
/*
 *  location: admin/controller
 */
class ControllerExtensionTotalProDiscount extends Controller
{
    private $codename = 'pro_discount';
    private $route = 'extension/total/pro_discount';
    private $type = 'total';

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

                // MODIFICATION
                $this->model_extension_pro_patch_modification->modificationHandler($this->codename, false);
                if (isset($parsed['status']) && $parsed['status']) {
                    $this->model_extension_pro_patch_modification->modificationHandler($this->codename, true);
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

    public function getState()
    {
        // HEADING
        $state['heading_title'] = $this->language->get('heading_title_main');

        $lng = array(
            'text_edit', 'text_yes', 'text_no', 'text_enabled', 'text_disabled',
            'text_deleted', 'text_undefined', 'text_status', 'text_success', 'text_warning',
            'text_close', 'text_cancel', 'text_setting', 'text_debug', 'text_no', 'text_yes',
            'text_fee', 'text_access_code', 'text_no_results', 'text_loading', 'text_sort_order',

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
            'href'      => $this->model_extension_pro_patch_url->getExtensionAjax('total'),
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
        $state['cancel'] = $this->model_extension_pro_patch_url->getExtensionAjax('total');
        $state['get_cancel'] = $this->model_extension_pro_patch_url->getExtensionAjax('total');
        $state['save'] = $this->model_extension_pro_patch_url->ajax("{$this->route}/save");

        $state['get_discounts'] = $this->model_extension_pro_patch_url->ajax("{$this->route}/get_discounts");
        $state['get_discount'] = $this->model_extension_pro_patch_url->ajax("{$this->route}/get_discount");
        $state['save_discount'] = $this->model_extension_pro_patch_url->ajax("{$this->route}/save_discount");
        $state['remove_discount'] = $this->model_extension_pro_patch_url->ajax("{$this->route}/remove_discount");
        $state['flip_discount_status'] = $this->model_extension_pro_patch_url->ajax("{$this->route}/flip_discount_status");

        $state['get_manufacturers'] = $this->model_extension_pro_patch_url->ajax("{$this->route}/get_manufacturers");
        $state['get_categories'] = $this->model_extension_pro_patch_url->ajax("{$this->route}/get_categories");
        $state['get_products'] = $this->model_extension_pro_patch_url->ajax("{$this->route}/get_products");
        $state['get_customers'] = $this->model_extension_pro_patch_url->ajax("{$this->route}/get_customers");

        // ALL
        $state['all_types'] = $this->extension_model->prepareForTree($this->extension_model->getAllTypes());
        $state['all_signs'] = $this->extension_model->prepareForTree($this->extension_model->getAllSigns());

        // SETTING
        $state['setting'] = $this->setting;
        if (isset($state['setting']['status']) && $state['setting']['status']) {
            $state['setting']['status'] = true;
        } else { $state['setting']['status'] = false; }

        // SET STATE
        return $state;
    }

    public function get_discounts()
    {
        $json['discounts'] = $this->extension_model->getDiscounts();

        $this->response->setOutput(json_encode($json));
    }

    public function get_discount()
    {
        $parsed = $this->model_extension_pro_patch_json->parseJson(file_get_contents('php://input'));

        if (isset($parsed['discount_id'])) {

            if (empty($parsed['discount_id'])) { $parsed['discount_id'] = null; }
            $json['discount'] = $this->extension_model->getDiscount($parsed['discount_id'], true);

            // ALL
            $json['all_manufacturers'] = $this->extension_model->prepareForTree(
                $this->extension_model->getAllManufacturers(null, $parsed['discount_id'], null));
            $json['all_categories'] = $this->extension_model->prepareForTree(
                $this->extension_model->getAllCategories(null, $parsed['discount_id'], null));
            $json['all_products'] = $this->extension_model->prepareForTree(
                $this->extension_model->getAllProducts(null, $parsed['discount_id'], null));
            $json['all_customers'] = $this->extension_model->prepareForTree(
                $this->extension_model->getAllCustomers(null, $parsed['discount_id'], null));

        } else {
            $json['error'][] = $this->language->get('error_corrupted_request');
        }

        $this->response->setOutput(json_encode($json));
    }

    public function save_discount()
    {
        $json = $this->model_extension_pro_patch_permission->validateRoute($this->route);

        if (!isset($json['error'])) {
            $parsed = $this->model_extension_pro_patch_json->parseJson(file_get_contents('php://input'));

            if (isset($parsed['discount'])) {
                $result = $this->extension_model->saveDiscount($parsed['discount']);
                $json = array_merge_recursive($json, $result);
            } else {
                $json['error'][] = $this->language->get('error_corrupted_request');
            }
        }

        $this->response->setOutput(json_encode($json));
    }

    public function remove_discount()
    {
        $json = $this->model_extension_pro_patch_permission->validateRoute($this->route);

        if (!isset($json['error'])) {
            $parsed = $this->model_extension_pro_patch_json->parseJson(file_get_contents('php://input'));

            if (isset($parsed['discount_id'])) {
                $result = $this->extension_model->removeDiscount($parsed['discount_id']);
            } else {
                $json['error'][] = $this->language->get('error_corrupted_request');
            }
        }

        $this->response->setOutput(json_encode($json));
    }

    public function flip_discount_status()
    {
        $json = $this->model_extension_pro_patch_permission->validateRoute($this->route);

        if (!isset($json['error'])) {
            $parsed = $this->model_extension_pro_patch_json->parseJson(file_get_contents('php://input'));

            if (isset($parsed['discount_id'])) {
                $json['discounts'] = $this->extension_model->flipDiscountStatus($parsed['discount_id']);
            } else {
                $json['error'][] = $this->language->get('error_corrupted_request');
            }
        }

        $this->response->setOutput(json_encode($json));
    }

    public function get_manufacturers()
    {
        $parsed = $this->model_extension_pro_patch_json->parseJson(file_get_contents('php://input'));

        $q = (isset($parsed['q'])) ? $parsed['q'] : null;
        $m = $this->extension_model->getAllManufacturers($q);
        $json['manufacturers'] = $this->extension_model->prepareForTree($m);

        $this->response->setOutput(json_encode($json));
    }

    public function get_categories()
    {
        $parsed = $this->model_extension_pro_patch_json->parseJson(file_get_contents('php://input'));

        $q = (isset($parsed['q'])) ? $parsed['q'] : null;
        $m = $this->extension_model->getAllCategories($q);
        $json['categories'] = $this->extension_model->prepareForTree($m);

        $this->response->setOutput(json_encode($json));
    }

    public function get_products()
    {
        $parsed = $this->model_extension_pro_patch_json->parseJson(file_get_contents('php://input'));

        $q = (isset($parsed['q'])) ? $parsed['q'] : null;
        $m = $this->extension_model->getAllProducts($q);
        $json['products'] = $this->extension_model->prepareForTree($m);

        $this->response->setOutput(json_encode($json));
    }

    public function get_customers()
    {
        $parsed = $this->model_extension_pro_patch_json->parseJson(file_get_contents('php://input'));

        $q = (isset($parsed['q'])) ? $parsed['q'] : null;
        $m = $this->extension_model->getAllCustomers($q);
        $json['customers'] = $this->extension_model->prepareForTree($m);

        $this->response->setOutput(json_encode($json));
    }

}