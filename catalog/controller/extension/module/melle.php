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
        $state['phone'] = $this->config->get('config_telephone');

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
            'filter' => false,
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

        $state['product_link_placeholder'] = $this->model_extension_pro_patch_url->ajax('product/product', '&product_id=', true);

        // GTM EVENTS
        $this->load->controller('extension/module/melle/initGTM');

        // LEADHIT
        $this->load->controller('extension/module/melle/initLeadhit');

        if (strcmp($this->model_tool_base->getPageType(), 'checkout') === 0) {
            $this->initCheckoutRP();
        }

        // SET STATE
        $this->document->addState($state['id'], json_encode($state));
        return $state;
    }

    public function renderHeaderContent($state)
    {
        $data = array();

        $data['base'] = $state['base'];
        $data['logo'] = $state['logo'];

        $data['phone'] = $state['phone'];
        $data['phoneLink'] = preg_replace('/\s+/', '', "tel:{$data['phone']}");

        $data['delivery_link'] = $state['delivery_link'];
        $data['is_logged'] = $state['is_logged'];
        $data['account_link'] = $state['account_link'];
        $data['logout_link'] = $state['logout_link'];
        $data['menu'] = $state['menu'];

        $data['search'] = $this->model_extension_pro_patch_load->view('common/search', array(
            'action' => $this->model_extension_pro_patch_url->ajax('product/search', '', true),
            'search_route' => 'product/search'
        ));

        return $this->model_extension_pro_patch_load->view("{$this->route}/header_prerender", $data);
    }

    public function initGTM()
    {
        // VARIABLE
        $state['id'] = "{$this->codename}_gtm";

        $pt = $this->model_tool_base->getPageType();
        $state['page_type'] = 'other';

        switch ($pt) {
            case 'checkout':
                $state['page_type'] = $pt;
                break;
            case 'category':
                $state['page_type'] = $pt;
                break;
            case 'product':
                $state['page_type'] = $pt;
                break;
            case 'search':
                $state['page_type'] = 'Search Results';
                break;
        }

        $state['related_products'] = array();

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

    public function initCheckoutRP()
    {
        // VARIABLE
        $state['id'] = "{$this->codename}_checkout_rp";

        $this->load->model('extension/module/pro_related');
        $state['cart_related_products'] =
            $this->model_extension_module_pro_related->prepareCartProducts();

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

            if ($this->session->data['customerActivated'] === $this->customer->getId()) {
                unset($this->session->data['customerActivated']);
                $state['customerActivated'] = true;
            }
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
        $this->load->model('extension/total/pro_discount');

        $product_info = $this->model_catalog_product->getProduct($product_id);

        if ($product_info) {
            // REVIEW
            $this->initProductReview($product_id);

            $this->load->model('extension/module/melle_product');
            $state['images'] = $this->model_extension_module_melle_product->prepareImagesFor($product_id);

            $state['productId'] = $product_id;
            $state['name'] = $product_info['h1'];
            $state['manufacturer'] = $product_info['manufacturer'];
            $state['currentCategory'] = $this->model_tool_base->getCurrentCategoryName();
            $state['quantity'] = 1;

            $this->load->model('extension/module/size_list');
            $state['sizeList'] = $this->model_extension_module_size_list->getSizeList($product_id);

            $defaultValues = $this->model_extension_module_super_offers->getDefaultValues($product_id, $product_info);

            $state['options'] = $this->model_extension_module_super_offers->getOptions($product_id, false);
            $fullCombinations = $this->model_extension_module_super_offers->getFullCombinations($product_id);

            // SPECIAL TEXT
            $state['star'] = false;
            $state['specialText'] = $this->model_extension_total_pro_discount->getSpecialText($product_id, false);
            if (strstr($state['specialText'], '*')) {
                $state['star'] = true;
                $state['specialText'] = trim(str_replace('*', '', $state['specialText']));
            }

            $this->load->model('catalog/review');
            $state['reviewCount'] = (int) $this->model_catalog_review->getTotalReviewsByProductId($product_id);
            $state['ratingValue'] = (float) $defaultValues['rating'];

            $state['ratingArray'] = (array) array();
            for ($i=0; $i < 5; $i++) {
                if ($defaultValues['rating'] > $i) {
                    $state['ratingArray'][] = true;
                    continue;
                }
                $state['ratingArray'][] = false;
            }

            /* RETAIL R START */
            $this->load->model('extension/module/offer_id');
            $fullCombinations = array_map(function($combination) {
                $combination['rr_product_id'] = $this->model_extension_module_offer_id->createAndReturnId($combination['import_id']);
                return $combination;
            }, $fullCombinations);

            $this->request->get['rr_product_id'] = $this->get_rr_product_id($fullCombinations);
            /* RETAIL R END */
        }

        $state['add_to_cart'] = $this->model_extension_pro_patch_url->ajax('checkout/cart/melle_add');
        $state['buy_one_click'] = $this->model_extension_pro_patch_url->ajax('checkout/cart/melle_oneclick');
        $state['getProductStock'] = $this->model_extension_pro_patch_url->ajax('extension/module/melle_product/getProductPreviewStock');

        // SET STATE
        $this->document->addState($state['id'], json_encode($state));
        return $state;
    }

    /* RETAIL R START */
    private function get_rr_product_id(array $combinations)
    {
        if (!empty($combinations)) {
            shuffle($combinations);
            $random_combination = array_pop($combinations);
            if (isset($random_combination['rr_product_id'])) {
                return $random_combination['rr_product_id'];
            }
        }

        return null;
    }
    /* RETAIL R END */

    public function renderProductContent($state)
    {
        $data = array();

        $data['product_id'] = $state['product_id'];
        $data['name'] = $state['name'];
        $data['manufacturer'] = $state['manufacturer'];
        $data['current_category'] = $state['current_category'];
        $data['quantity'] = 1;

        $data['options'] = $state['options'];
        $data['size_list'] = $state['size_list'];

        $data['in_stock'] = (bool) $state['in_stock'];
        $data['is_options_for_product'] = $state['is_options_for_product'];

        $data['zvezdochka'] = isset($state['zvezdochka']) ? $state['zvezdochka'] : false;
        $data['special_text'] = $state['special_text'];

        $data['getSpecial'] = $state['default_values']['special'];

        $data['isSpecial'] = $data['getSpecial'] > 0 ? true : false;
        $data['getActivePrice'] = $state['default_values']['price'];

        $data['getRating'] = array();
        for ($i=0; $i < 5; $i++) {
            if ((int) $state['default_values']['rating'] > 0
            && (int) $state['default_values']['rating'] > $i) {
                $data['getRating'][] = true;
            } else {
                $data['getRating'][] = false;
            }
        }

        $data['reviewCount'] = $state['reviewCount'];
        $data['ratingValue'] = $state['ratingValue'];

        return $this->model_extension_pro_patch_load->view("{$this->route}/product_prerender", $data);
    }

    public function initProductReview($product_id)
    {
        // VARIABLE
        $state['id'] = "{$this->codename}_review";

        $state['product_id'] = $product_id;
        $state['review_link'] = $this->model_extension_pro_patch_url->ajax('product/product/melle_add_review', '', true);

        $this->load->model('catalog/review');
        $state['reviews'] = array_map(function($review) {
            return array(
                'review_id' => $review['review_id'],
                'author' => $review['author'],
                'date_added' => $review['date_added'],
                'rating' => (int) $review['rating'],
                'text' => $review['text'],
            );
        }, $this->model_catalog_review->getReviewsByProductId($state['product_id']));

        // SET STATE
        $this->document->addState($state['id'], json_encode($state));
    }

    public function initCatalog($data)
    {
        // VARIABLE
        $state['id'] = "{$this->codename}_catalog";

        $state['heading_title'] = isset($data['heading_title']) ? $data['heading_title'] : '';
        $state['breadcrumbs'] = isset($data['breadcrumbs']) ? $data['breadcrumbs'] : [];

        $this->load->model('catalog/super');
        $state = array_merge($state, $this->model_catalog_super->getProducts());

        $state['design_col'] = true;
        $state['current_category'] = $this->model_tool_base->getCurrentCategoryName();
        $state['get_link'] = $this->model_extension_pro_patch_url->ajax('product/category/melle_get', '', true);
        $state['getProductPreviewData'] = $this->model_extension_pro_patch_url
            ->ajax("extension/module/melle_product/getProductPreviewData", '', true);
        $state['getProductFullData'] = $this->model_extension_pro_patch_url
            ->ajax("extension/module/melle_product/getProductFullData", '', true);
        $state['getProductPreviewStock'] = $this->model_extension_pro_patch_url
            ->ajax("extension/module/melle_product/getProductPreviewStock", '', true);

        // SET STATE
        $this->document->addState($state['id'], json_encode($state));
        return $state;
    }

    public function initSearch()
    {
        // VARIABLE
        $state['id'] = "{$this->codename}_search";

        $state['pageRoute'] = 'product/search';
        $state['searchQuery'] = '';
        $state['productLinkPlaceholder'] = $this->model_extension_pro_patch_url->ajax('product/product', '&product_id=', true);

        // SET STATE
        $this->document->addState($state['id'], json_encode($state));
        return $state;
    }

    public function renderCatalogContent($state)
    {
        $data = array();
        $data['products'] = array();

        foreach ($state['products'] as $p) {
            $product = array(
                'product_id' => $p['product_id'],
                'href' => $p['href'],
                'name' => $p['name'],
                'h1' => $p['h1'],
                'manufacturer' => $p['manufacturer'],
                'image' => $p['image'],
                'zvezdochka' => $p['zvezdochka'],
                'znachek' => $p['znachek'],
                'znachek_class' => $p['znachek_class'],
                'special_text' => $p['special_text'],

                'getPrice' => $p['default_values']['price'],
                'getSpecial' => $p['default_values']['special'],
                'isSpecial' => false,
                'in_stock' => $p['in_stock'],
            );

            if ($product['getSpecial'] !== false
            && preg_replace('/\s+/', '', $product['getSpecial']) > 0) {
                $product['isSpecial'] = true;
            }

            $data['products'][] = $product;
        }

        $data['current_category'] = $state['current_category'];
        $data['product_total'] = $state['product_total'];

        $data['canLoadMore'] = false;
        if ($data['product_total'] > 0
        && $data['product_total'] > count($data['products'])) {
            $data['canLoadMore'] = true;
        }

        return $this->model_extension_pro_patch_load->view("{$this->route}/catalog_prerender", $data);
    }

    public function initFilter()
    {
        // VARIABLE
        $state['id'] = "{$this->codename}_filter";

        $this->load->model('catalog/super');
        $state['filter_data'] = $this->model_catalog_super->getFilterValues();
        $state['last_filter'] = $state['filter_data'];
        $state['slider_options'] = $this->model_catalog_super->prepareSliderOptions();
        $state['query_params'] = $this->model_catalog_super->getDefaultFilterQueryParams();

        // SET STATE
        $this->document->addState($state['id'], json_encode($state));
        return $state;
    }

    public function renderFilterContent($state)
    {
        $data = array();

        $data['product_total'] = 0;
        $data['manufacturers'] = $state['filter_data']['manufacturers'];
        $data['all_sizes'] = $state['filter_data']['all_sizes'];
        $data['all_colors'] = $state['filter_data']['all_colors'];
        $data['all_materials'] = $state['filter_data']['all_materials'];

        $data['min_price'] = $state['filter_data']['min_price'];
        $data['max_price'] = $state['filter_data']['max_price'];
        $data['min_den'] = $state['filter_data']['min_den'];
        $data['max_den'] = $state['filter_data']['max_den'];

        $data['hit'] = $state['filter_data']['hit'];
        $data['neww'] = $state['filter_data']['neww'];
        $data['act'] = $state['filter_data']['act'];

        return $this->model_extension_pro_patch_load->view("{$this->route}/filter_prerender", $data);
    }

    public function initLeadhit()
    {
        // VARIABLE
        $state['id'] = "{$this->codename}_leadhit";

        $this->load->model('extension/module/leadhit');

        $state = array_merge($state, $this->model_extension_module_leadhit->getSetting());

        // SET STATE
        $this->document->addState($state['id'], json_encode($state));
        return $state;
    }
}
