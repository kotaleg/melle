<?php

class ControllerExtensionPaymentRbs extends Controller
{
    /**
     * Инициализация языкового пакета
     * @param $registry
     */
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->load->language('extension/payment/rbs');
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/payment/rbs.tpl')) {
            $this->have_template = true;
        }
    }

    /**
     * Рендеринг кнопки-ссылки для перехода в метод payment()
     * @return mixed Шаблон кнопки
     */
    public function index()
    {
        $data['action'] = $this->url->link('extension/payment/rbs/payment');
        $data['button_confirm'] = $this->language->get('button_confirm');
        return $this->get_template('extension/payment/rbs', $data);
    }

    /**
     * Отрисовка шаблона
     * @param $template     Шаблон вместе с корневой папкой
     * @param $data         Данные
     * @return mixed        Отрисованный шаблон
     */
    private function get_template($template, $data)
    {
        return $this->load->view($template, $data);
    }

    /**
     * Регистрация заказа.
     * Переадресация покупателя при успешной регистрации.
     * Вывод ошибки при неуспешной регистрации.
     */
    public function payment()
    {

        // for config settings
        $this->initializeRbs();

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $order_number = $this->session->data['order_id'];
        $amount = $order_info['total'] * 100;
        $return_url = $this->url->link('extension/payment/rbs/callback');


        // here we will collect data for orderBundle
        $orderBundle = [];

        foreach ($this->cart->getProducts() as $product) {

            $itemCode = $product['product_id'];
            $name = $product['name'];

            /* EXTRA NAME START */
            try {
                $this->load->model('catalog/product');
                $this->load->model('api/export');

                $productExtra = $this->model_catalog_product->getProduct($product['product_id']);
                $itemCode = isset($productExtra['name']) ? $productExtra['name'] : $itemCode;
                $name = $this->model_api_export->getClosestCategoryNameForProduct($product['product_id']);

                if (isset($productExtra['name']) && $productExtra['name']) {
                    if (strpos($productExtra['name'], ' ')) {
                        $nameEx = explode(' ', $productExtra['name']);
                        $nameEx = array_shift($nameEx);
                    } else {
                        $nameEx = $productExtra['name'];
                    }

                    if ($nameEx) {
                        $name .= " {$nameEx}";
                    }
                }

            } catch (\Exception $e) {
                $this->log->write($e->getMessage());
            }
            /* EXTRA NAME START */

            /* RECALCULATE DISCOUNT FROM TOTAL START */
            if ($product['price'] !== ($product['total'] * $product['quantity'])) {
                $product['price'] = $product['total'] / $product['quantity'];
                $product['price'] = round($product['price'], 2);
                $product['total'] = round($product['price'] * $product['quantity'], 2);
            }
            /* RECALCULATE DISCOUNT FROM TOTAL END */

            /* PRODUCT COUPON START */
            if (isset($this->session->data['coupon'])) {
                $this->load->model('extension/total/coupon');
                $couponInfo = $this->model_extension_total_coupon->getCoupon($this->session->data['coupon']);

                if ($couponInfo) {
                    if (!$couponInfo['product']) {
                        $couponStatus = true;
                    } else {
                        $couponStatus = in_array($product['product_id'], $couponInfo['product']);
                    }

                    if ($couponStatus) {
                        if ($couponInfo['type'] == 'F') {
                            // TODO
                        } elseif ($couponInfo['type'] == 'P') {
                            $product['price'] = $product['price'] - ($product['price'] / 100 * $couponInfo['discount']);
                            $product['price'] = round($product['price'], 2);
                            $product['total'] = round($product['price'] * $product['quantity'], 2);
                        }
                    }
                }
            }
            /* PRODUCT COUPON END */

            $product_taxSum = $this->tax->getTax($product['price'], $product['tax_class_id']);
            $product_amount = ( $product['price'] + $product_taxSum ) * $product['quantity']; 

            $product_data[] = array(
                'positionId' => $product['cart_id'],
                'name' => $name,
                'quantity' => array(
                    'value' => $product['quantity'],
                    //todo fix piece
                    'measure' => "шт"
                ),
                'itemAmount' => $product_amount * 100,
                'itemCode' => $itemCode,

                'tax' => array(
                    // todo: some question taxType
                    'taxType' => $this->config->get('payment_rbs_taxType'),
                    'taxSum' => $product_taxSum * 100
                ),
                'itemPrice' => ($product['price'] + $product_taxSum) * 100,
            );

            $orderBundle['cartItems']['items'] = $product_data;
        }

        // Delivery calculations:
        if (isset($this->session->data['shipping_method']['cost']) && $this->session->data['shipping_method']['cost'] > 0) {

            $delivery['positionId'] = 'delivery';
            $delivery['name'] = $this->session->data['shipping_method']['title'];
            $delivery['itemAmount'] = $this->session->data['shipping_method']['cost'] * 100;
            $delivery['quantity']['value'] = 1;
            //todo fix piece
            $delivery['quantity']['measure'] = 'шт';
            $delivery['itemCode'] = $this->session->data['shipping_method']['code'];
            $delivery['tax']['taxType'] = $this->config->get('payment_rbs_taxType');
            $delivery['tax']['taxSum'] = 0;
            $delivery['itemPrice'] = $this->session->data['shipping_method']['cost'] * 100;
            $orderBundle['cartItems']['items'][] = $delivery;
        }

        /* RECALCULATE TOTAL BUNDLE ITEMS START */
        $bundleAmount = 0;
        foreach ($orderBundle['cartItems']['items'] as $bunbleCartItem) {
            $bundleAmount += $bunbleCartItem['itemAmount'];
        }

        if (($bundleAmount - $amount) > 1) {
            $this->log->write(":: RBS :: `amount` => {$amount}  `bundleAmount` => {$bundleAmount}");
        }
        /* RECALCULATE TOTAL BUNDLE ITEMS END */ 

        $response = $this->rbs->register_order($order_number, $bundleAmount, $return_url, $orderBundle);

        if (isset($response['errorCode'])) {
            $this->document->setTitle($this->language->get('error_title'));

            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['button_continue'] = $this->language->get('error_continue');

            $data['heading_title'] = $this->language->get('error_title') . ' #' . $response['errorCode'];
            $data['text_error'] = $response['errorMessage'];
            $data['continue'] = $this->url->link('checkout/cart');

            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');

            $this->response->setOutput($this->get_template('error/rbs', $data));
        } else {
            $this->response->redirect($response['formUrl']);
        }
    }

    /**
     * Инициализация библиотеки RBS
     */
    private function initializeRbs()
    {
        $this->library('rbs');
        $this->rbs = new RBS();
        $this->rbs->login = $this->config->get('payment_rbs_merchantLogin');
        $this->rbs->password = $this->config->get('payment_rbs_merchantPassword');
        $this->rbs->stage = $this->config->get('payment_rbs_stage');
        $this->rbs->mode = $this->config->get('payment_rbs_mode');
        $this->rbs->logging = $this->config->get('payment_rbs_logging');
        $this->rbs->currency = $this->config->get('payment_rbs_currency');
        $this->rbs->taxSystem = $this->config->get('payment_rbs_taxSystem');
        $this->rbs->taxType = $this->config->get('payment_rbs_taxSystem');
        $this->rbs->ofd_status = $this->config->get('payment_rbs_ofd_status');
        $this->rbs->language = $this->language->get('code');
    }

    /**
     * В версии 2.1 нет метода Loader::library()
     * Своя реализация
     * @param $library
     */
    private function library($library)
    {
        $file = DIR_SYSTEM . 'library/' . str_replace('../', '', (string)$library) . '.php';

        if (file_exists($file)) {
            include_once($file);
        } else {
            trigger_error('Error: Could not load library ' . $file . '!');
            exit();
        }
    }

    /**
     * Колбек для возвращения покупателя из ПШ в магазин.
     */
    public function callback()
    {
        if (isset($this->request->get['orderId'])) {
            $order_id = $this->request->get['orderId'];
        } else {
            die('Illegal Access');
        }

        $this->load->model('checkout/order');
        $order_number = $this->session->data['order_id'];
        $order_info = $this->model_checkout_order->getOrder($order_number);

        if ($order_info) {
            $this->initializeRbs();

            $response = $this->rbs->get_order_status($order_id);
            if (($response['errorCode'] == 0) && (($response['orderStatus'] == 1) || ($response['orderStatus'] == 2))) {

                // set order status
                $this->model_checkout_order->addOrderHistory($order_number, $this->config->get('payment_rbs_order_status_id'));

                $this->response->redirect($this->url->link('checkout/success', '', true));
            } else {
                $this->response->redirect($this->url->link('checkout/failure', '', true));
            }
        }
    }

}