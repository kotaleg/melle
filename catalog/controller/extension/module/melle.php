<?php
/*
 *  location: catalog/controller
 */
class ControllerExtensionModuleMelle extends Controller
{
    private $codename = 'melle';
    private $route = 'extension/module/melle';
    private $type = 'module';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language($this->route);

        $this->load->model($this->route);
        $this->load->model('extension/pro_patch/url');
        $this->load->model('extension/pro_patch/load');
        $this->load->model('extension/pro_patch/user');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/setting');
        $this->load->model('tool/base');

        $this->extension_model = $this->{'model_'.str_replace("/", "_", $this->route)};
    }

    public function index()
    {
        ///
    }

    public function getMobileMenu()
    {
        // MOBILE MENU
        $this->document->addScript('catalog/view/javascript/melle/query/mmenu-old/jquery.mmenu.all.js');

        $data['menu'] = $this->extension_model->getMenu();

        return $this->model_extension_pro_patch_load->view("{$this->route}/mobile_menu", $data);
    }

    public function initHeader()
    {
        // VARIABLE
        $state['id'] = "{$this->codename}_header";

        $lng = array(
            'text_success', 'text_warning',
        );
        for ($i = 0; $i < sizeof($lng); $i++) { $state[$lng[$i]] = $this->language->get($lng[$i]); }

        $state['base'] = $this->model_tool_base->getBase();
        $state['phone'] = '8 800 777 21 73';

        if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
            $state['logo'] = $state['base'] . 'image/' . $this->config->get('config_logo');
        } else {
            $state['logo'] = '';
        }

        $state['menu'] = $this->extension_model->getMenu();

        $state['sidebar_opened'] = false;
        $state['elements'] = array(
            'mail_us' => false,
            'login' => false,
            'register' => false,
            'filter' => false,
            'cart' => false,
            'forgotten' => false,
        );

        $state['captcha'] = array(
            'sitekey' => '',
        );

        if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status')) {
            $state['captcha']['sitekey'] = $this->load->controller(
                "extension/captcha/{$this->config->get('config_captcha')}/getKey");
        }

        $state['is_loading'] = false;
        $state['is_sidebar_loading'] = false;
        $state['is_logged'] = $this->customer->isLogged();

        $state['login_link'] = $this->model_extension_pro_patch_url->ajax('account/login/melle_login', '', true);
        $state['logout_link'] = $this->model_extension_pro_patch_url->ajax('account/logout', '', true);
        $state['register_link'] = $this->model_extension_pro_patch_url->ajax('account/register/melle_register', '', true);
        $state['forgotten_link'] = $this->model_extension_pro_patch_url->ajax('account/forgotten/melle_forgotten', '', true);
        $state['account_link'] = $this->model_extension_pro_patch_url->ajax('account/account', '', true);
        $state['mail_us_link'] = $this->model_extension_pro_patch_url->ajax('information/contact/melle_mail_us', '', true);
        $state['captcha_link'] = $this->model_extension_pro_patch_url->ajax(
            "extension/captcha/{$this->config->get('config_captcha')}/melle_validate", '', true);

        $state['konfidentsialnost_link'] = $this->model_extension_pro_patch_url->ajax('information/information', '&information_id=3', true);
        $state['public_offer_link'] = $this->model_extension_pro_patch_url->ajax('information/information', '&information_id=5', true);
        $state['delivery_link'] = $this->model_extension_pro_patch_url->ajax('information/information', '&information_id=8', true);

        // SET STATE
        $this->document->addState($state['id'], json_encode($state));
    }

    public function initCart()
    {
        // VARIABLE
        $state['id'] = "{$this->codename}_cart";

        $state['catalog_link'] = $this->model_extension_pro_patch_url->ajax('common/home');
        $state['checkout_link'] = $this->model_extension_pro_patch_url->ajax('checkout/checkout');

        $state['add_to_cart'] = $this->model_extension_pro_patch_url->ajax('checkout/cart/melle_add', '', true);
        $state['get_data'] = $this->model_extension_pro_patch_url->ajax('checkout/cart/melle_get_data', '', true);
        $state['remove'] = $this->model_extension_pro_patch_url->ajax('checkout/cart/melle_remove', '', true);
        $state['update'] = $this->model_extension_pro_patch_url->ajax('checkout/cart/melle_update', '', true);
        $state['clear'] = $this->model_extension_pro_patch_url->ajax('checkout/cart/melle_clear', '', true);
        $state['buy_one_click'] = $this->model_extension_pro_patch_url->ajax('checkout/cart/melle_oneclick', '', true);

        $this->load->model('tool/base');
        if (strcmp($this->model_tool_base->getPageType(), 'checkout') === 0) {
            $state['is_checkout'] = true;
        } else {
            $state['is_checkout'] = false;
        }

        $this->load->model('checkout/cart');
        $cart_data = $this->model_checkout_cart->getCart();
        $state = array_merge($state, $cart_data);

        // SET STATE
        $this->document->addState($state['id'], json_encode($state));
    }

    public function initAccount()
    {
        // VARIABLE
        $state['id'] = "{$this->codename}_account";

        $this->load->model('account/customer');
        $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

        if ($customer_info) {
            $state['form'] = array(
                'name' => "{$customer_info['firstname']} {$customer_info['lastname']}",
                'email' => $customer_info['email'],
                'phone' => $customer_info['telephone'],
                'password' => '',
                'confirm' => '',
                'birth' => $customer_info['birth'],
                'discount_card' => $customer_info['discount_card'],
                'newsletter' => ($customer_info['newsletter']) ? true : false,
            );
        }

        $state['edit_link'] = $this->model_extension_pro_patch_url->ajax('account/edit/melle_edit', '', true);

        // SET STATE
        $this->document->addState($state['id'], json_encode($state));
    }

    public function initProduct($product_id)
    {
        // VARIABLE
        $state['id'] = "{$this->codename}_product";

        $this->load->language('product/product');
        $this->load->model('catalog/product');
        $this->load->model('extension/module/super_offers');

        $product_info = $this->model_catalog_product->getProduct($product_id);

        if ($product_info) {
            $state['product_id'] = $product_id;
            $state['quantity'] = 1;

            $state['default_values'] = $this->model_extension_module_super_offers->getDefaultValues($product_id, $product_info);

            $state['is_options_for_product'] = (bool)$this->model_extension_module_super_offers->isOptionsForProduct($product_id);
            $state['options'] = $this->model_extension_module_super_offers->getOptions($product_id);
            // $state['original_options'] = $state['options'];
            $state['combinations_for_options'] = $this->model_extension_module_super_offers->getCombinationsForOptions(
                $product_id, $state['options']);
            $state['active_options'] = array();
        }

        $state['add_to_cart'] = $this->model_extension_pro_patch_url->ajax('checkout/cart/melle_add', '', true);
        $state['buy_one_click'] = $this->model_extension_pro_patch_url->ajax('checkout/cart/melle_oneclick', '', true);

        // SET STATE
        $this->document->addState($state['id'], json_encode($state));
    }

    public function initCatalog()
    {
        // VARIABLE
        $state['id'] = "{$this->codename}_catalog";

        $this->load->model('catalog/super');
        $state = array_merge($state, $this->model_catalog_super->getProducts());

        $state['design_col'] = true;
        $state['get_link'] = $this->model_extension_pro_patch_url->ajax('product/category/melle_get', '', true);

        // SET STATE
        $this->document->addState($state['id'], json_encode($state));
    }

    public function initFilter()
    {
        // VARIABLE
        $state['id'] = "{$this->codename}_filter";

        $this->load->model('catalog/super');
        $state['filter_data'] = $this->model_catalog_super->getFilterValues();
        $state['last_filter'] = $state['filter_data'];
        $state['slider_options'] = $this->model_catalog_super->prepareSliderOptions();

        // SET STATE
        $this->document->addState($state['id'], json_encode($state));
    }
}