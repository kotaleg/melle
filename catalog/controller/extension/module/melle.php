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

        $state['konfidentsialnost_link'] = $this->model_extension_pro_patch_url->ajax('account/login/melle_login', '', true);
        $state['public_offer_link'] = $this->model_extension_pro_patch_url->ajax('account/login/melle_login', '', true);
        $state['delivery_link'] = $this->model_extension_pro_patch_url->ajax('information/information', '&information_id=8', true);

        // SET STATE
        $this->document->addState($state['id'], json_encode($state));
    }

    public function initCart()
    {
        // VARIABLE
        $state['id'] = "{$this->codename}_cart";

        $state['catalog_link'] = $this->model_extension_pro_patch_url->ajax('common/home');
        $state['cart_link'] = $this->model_extension_pro_patch_url->ajax('checkout/cart');
        $state['checkout_link'] = $this->model_extension_pro_patch_url->ajax('checkout/checkout');

        $state['count'] = $this->cart->countProducts();
        $state['products'] = array();

        if ($this->cart->hasProducts() || !empty($this->session->data['vouchers'])) {
            $this->load->model('tool/image');
            $this->load->model('tool/upload');

            $products = $this->cart->getProducts();

            foreach ($products as $product) {
                $product_total = 0;

                foreach ($products as $product_2) {
                    if ($product_2['product_id'] == $product['product_id']) {
                        $product_total += $product_2['quantity'];
                    }
                }

                if ($product['minimum'] > $product_total) {
                    $state['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
                }

                if ($product['image']) {
                    $image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height'));
                } else {
                    $image = '';
                }

                $option_data = array();

                foreach ($product['option'] as $option) {
                    if ($option['type'] != 'file') {
                        $value = $option['value'];
                    } else {
                        $upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

                        if ($upload_info) {
                            $value = $upload_info['name'];
                        } else {
                            $value = '';
                        }
                    }

                    $option_data[] = array(
                        'name'  => $option['name'],
                        'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
                    );
                }

                // Display prices
                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));

                    $price = $this->currency->format($unit_price, $this->session->data['currency']);
                    $total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
                } else {
                    $price = false;
                    $total = false;
                }

                $state['products'][] = array(
                    'cart_id'   => $product['cart_id'],
                    'thumb'     => $image,
                    'name'      => $product['name'],
                    'model'     => $product['model'],
                    'option'    => $option_data,
                    'recurring' => $recurring,
                    'quantity'  => $product['quantity'],
                    'max_quantity' => 0,
                    'stock'     => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
                    'reward'    => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
                    'price'     => $price,
                    'total'     => $total,
                    'href'      => $this->url->link('product/product', 'product_id=' . $product['product_id'])
                );
            }
        }

        // Totals
        $this->load->model('setting/extension');

        $totals = array();
        $taxes = $this->cart->getTaxes();
        $total = 0;

        // Because __call can not keep var references so we put them into an array.
        $total_data = array(
            'totals' => &$totals,
            'taxes'  => &$taxes,
            'total'  => &$total
        );

        // Display prices
        if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
            $sort_order = array();

            $results = $this->model_setting_extension->getExtensions('total');

            foreach ($results as $key => $value) {
                $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
            }

            array_multisort($sort_order, SORT_ASC, $results);

            foreach ($results as $result) {
                if ($this->config->get('total_' . $result['code'] . '_status')) {
                    $this->load->model('extension/total/' . $result['code']);

                    // We have to put the totals in an array so that they pass by reference.
                    $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
                }
            }

            $sort_order = array();

            foreach ($totals as $key => $value) {
                $sort_order[$key] = $value['sort_order'];
            }

            array_multisort($sort_order, SORT_ASC, $totals);
        }

        $state['total'] = 0;
        $state['totals'] = array();

        foreach ($totals as $total) {
            if ($total) {

            }

            $state['totals'][] = array(
                'title' => $total['title'],
                'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
            );
        }

        // SET STATE
        $this->document->addState($state['id'], json_encode($state));
    }
}