<?php
class ControllerAccountAccount extends Controller {
    public function index() {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/account', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->load->language('account/account');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_account'),
            'href' => $this->url->link('account/account', '', true)
        );

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['edit'] = $this->url->link('account/edit', '', true);
        $data['password'] = $this->url->link('account/password', '', true);
        $data['address'] = $this->url->link('account/address', '', true);

        $data['credit_cards'] = array();

        $files = glob(DIR_APPLICATION . 'controller/extension/credit_card/*.php');

        foreach ($files as $file) {
            $code = basename($file, '.php');

            if ($this->config->get('payment_' . $code . '_status') && $this->config->get('payment_' . $code . '_card')) {
                $this->load->language('extension/credit_card/' . $code, 'extension');

                $data['credit_cards'][] = array(
                    'name' => $this->language->get('extension')->get('heading_title'),
                    'href' => $this->url->link('extension/credit_card/' . $code, '', true)
                );
            }
        }

        $data['wishlist'] = $this->url->link('account/wishlist');
        $data['order'] = $this->url->link('account/order', '', true);
        $data['download'] = $this->url->link('account/download', '', true);

        if ($this->config->get('total_reward_status')) {
            $data['reward'] = $this->url->link('account/reward', '', true);
        } else {
            $data['reward'] = '';
        }

        $data['return'] = $this->url->link('account/return', '', true);
        $data['transaction'] = $this->url->link('account/transaction', '', true);
        $data['newsletter'] = $this->url->link('account/newsletter', '', true);
        $data['recurring'] = $this->url->link('account/recurring', '', true);

        $this->load->model('account/customer');

        $affiliate_info = $this->model_account_customer->getAffiliate($this->customer->getId());

        if (!$affiliate_info) {
            $data['affiliate'] = $this->url->link('account/affiliate/add', '', true);
        } else {
            $data['affiliate'] = $this->url->link('account/affiliate/edit', '', true);
        }

        if ($affiliate_info) {
            $data['tracking'] = $this->url->link('account/tracking', '', true);
        } else {
            $data['tracking'] = '';
        }

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/account', $data));
    }

    /* IVAN MOD START */
    public function activateAccount()
    {
        if (isset($this->request->get['approveToken'])) {
            $this->load->model('extension/module/melle');
            $customer_id = $this->model_extension_module_melle->activateToken($this->request->get['approveToken']);

            if ($customer_id) {
                $this->load->model('account/customer');
                // $this->model_account_customer->updateCustomerStatus($customer_id, true);

                $this->customer->logout();
                $this->cart->clear();

                unset($this->session->data['order_id']);
                unset($this->session->data['payment_address']);
                unset($this->session->data['payment_method']);
                unset($this->session->data['payment_methods']);
                unset($this->session->data['shipping_address']);
                unset($this->session->data['shipping_method']);
                unset($this->session->data['shipping_methods']);
                unset($this->session->data['comment']);
                unset($this->session->data['coupon']);
                unset($this->session->data['reward']);
                unset($this->session->data['voucher']);
                unset($this->session->data['vouchers']);

                $this->session->data['customerActivated'] = $customer_id;

                $customer_info = $this->model_account_customer->getCustomer($customer_id);
                if ($customer_info && $this->customer->login($customer_info['email'], '', true)) {
                    $this->response->redirect($this->url->link('account/account', '', true));
                }
            }
        }

        $this->response->redirect($this->url->link('common/home', '', true));
    }
    /* IVAN MOD END */

    public function country() {
        $json = array();

        $this->load->model('localisation/country');

        $country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

        if ($country_info) {
            $this->load->model('localisation/zone');

            $json = array(
                'country_id'        => $country_info['country_id'],
                'name'              => $country_info['name'],
                'iso_code_2'        => $country_info['iso_code_2'],
                'iso_code_3'        => $country_info['iso_code_3'],
                'address_format'    => $country_info['address_format'],
                'postcode_required' => $country_info['postcode_required'],
                'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
                'status'            => $country_info['status']
            );
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
