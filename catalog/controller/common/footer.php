<?php
class ControllerCommonFooter extends Controller {
    public function index() {
        $this->load->language('common/footer');

        $this->load->model('catalog/information');

        $data['informations'] = array();

        foreach ($this->model_catalog_information->getInformations() as $result) {
            if ($result['bottom']) {
                $data['informations'][] = array(
                    'link'  => (isset($result['link']) && $result['link']) ? $result['link'] : false,
                    'title' => $result['title'],
                    'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
                );
            }
        }

        $data['contact'] = $this->url->link('information/contact');
        $data['return'] = $this->url->link('account/return/add', '', true);
        $data['sitemap'] = $this->url->link('information/sitemap');
        $data['tracking'] = $this->url->link('information/tracking');
        $data['manufacturer'] = $this->url->link('product/manufacturer');
        $data['voucher'] = $this->url->link('account/voucher', '', true);
        $data['affiliate'] = $this->url->link('affiliate/login', '', true);
        $data['special'] = $this->url->link('product/special');
        $data['account'] = $this->url->link('account/account', '', true);
        $data['order'] = $this->url->link('account/order', '', true);
        $data['wishlist'] = $this->url->link('account/wishlist', '', true);
        $data['newsletter'] = $this->url->link('account/newsletter', '', true);

        $data['powered'] = $this->language->get('text_powered');

        // Whos Online
        if ($this->config->get('config_customer_online')) {
            $this->load->model('tool/online');

            if (isset($this->request->server['REMOTE_ADDR'])) {
                $ip = $this->request->server['REMOTE_ADDR'];
            } else {
                $ip = '';
            }

            if (isset($this->request->server['HTTP_HOST']) && isset($this->request->server['REQUEST_URI'])) {
                $url = ($this->request->server['HTTPS'] ? 'https://' : 'http://') . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI'];
            } else {
                $url = '';
            }

            if (isset($this->request->server['HTTP_REFERER'])) {
                $referer = $this->request->server['HTTP_REFERER'];
            } else {
                $referer = '';
            }

            $this->model_tool_online->addOnline($ip, $this->customer->getId(), $url, $referer);
        }

        $data['scripts'] = $this->document->getScripts('footer');

        $this->load->model('tool/base');
        $data['base'] = $this->model_tool_base->getBase();
        $data['pagetype'] = $this->model_tool_base->getPageType();
        $data['is_local'] = $this->model_tool_base->isLocal();

        $this->load->model('extension/module/melle');
        $data['menu'] = array_map(function($item) {
            $item['children'] = array_slice($item['children'], 0, 5);
            return $item;
        }, $this->model_extension_module_melle->getMenu());

        $data['phone'] = $this->config->get('config_telephone');
        $data['phoneLink'] = preg_replace('/\s+/', '', "tel:{$data['phone']}");

        /* RETAIL R START */
        if (isset($this->request->get['rr_product_id'])) {
            $data['rr_product_id'] = $this->request->get['rr_product_id'];
        }
        if (isset($this->request->get['rr_category_id'])) {
            $data['rr_category_id'] = $this->request->get['rr_category_id'];
        }
        /* RETAIL R END */

        // SUCCESS DATA
        $data['success_data'] = false;

        if ($data['pagetype'] == 'checkout-success' && isset($this->session->data['sp_order_id'])) {
            $data['success_data'] = array();
            $data['success_data']['order_id'] = $this->session->data['sp_order_id'];
            unset($this->session->data['sp_order_id']);

            $this->load->model('checkout/order');
            $this->load->model('extension/module/super_offers');

            $orderInfo = $this->model_checkout_order->getOrder($data['success_data']['order_id']);
            if (isset($orderInfo['email']) && isset($orderInfo['firstname']) && isset($orderInfo['lastname'])) {
                $data['success_data']['email'] = $orderInfo['email'];
                $data['success_data']['name'] = $orderInfo['firstname'] . $orderInfo['lastname'];
            }

            if (isset($orderInfo['total'])) {
                $data['success_data']['total'] = number_format($orderInfo['total'], 2);
            }

            $order_offers = array();
            foreach ($this->model_checkout_order->getOrderProducts($data['success_data']['order_id']) as $p) {
                $order_offers[] = array(
                    'url' => (string) $this->url->link('product/product', 'product_id=' . (int)$p['product_id'], true),
                    'name' => (string) $p['name'],
                    'price' => (float) $p['price'],
                    'count' => (int) $p['quantity'],
                    'currency' => 'RUB'
                );
            }

            $data['success_data']['order_offers'] = json_encode($order_offers);
        }

        return $this->load->view('common/footer', $data);
    }
}
