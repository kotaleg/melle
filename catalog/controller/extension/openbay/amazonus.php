<?php
class ControllerExtensionOpenbayAmazonus extends Controller {
    public function order() {
        if ($this->config->get('openbay_amazonus_status') != '1') {
            return;
        }

        $this->load->model('checkout/order');
        $this->load->model('extension/openbay/amazonus_order');
        $this->load->language('extension/openbay/amazonus_order');

        $logger = new Log('amazonus.log');
        $logger->write('amazonus/order - started');

        $incoming_token = isset($this->request->post['token']) ? $this->request->post['token'] : '';

        if (!hash_equals($this->config->get('openbay_amazonus_token'), $incoming_token)) {
            $logger->write('amazon/order - Incorrect token: ' . $incoming_token);
            return;
        }

        $decrypted = $this->openbay->decrypt($this->request->post['data'], $this->openbay->amazonus->getEncryptionKey(), $this->openbay->amazonus->getEncryptionIv(), false);

        if (!$decrypted) {
            $logger->write('amazonus/order Failed to decrypt data');
            return;
        }

        $order_xml = simplexml_load_string(base64_decode($decrypted));

        $amazonus_order_status = trim(strtolower((string)$order_xml->Status));

        $amazonus_order_id = (string)$order_xml->AmazonOrderId;
        $order_status = $this->model_extension_openbay_amazonus_order->getMappedStatus((string)$order_xml->Status);

        $logger->write('Received order ' . $amazonus_order_id);

        $order_id = $this->model_extension_openbay_amazonus_order->getOrderId($amazonus_order_id);

        // If the order already exists on opencart, ignore it.
        if ($order_id) {
            $logger->write("Duplicate order $amazonus_order_id. Terminating.");
            $this->response->setOutput('Ok');
            return;
        }

        /* Check if order comes from subscribed marketplace */

        $currency_to = $this->config->get('config_currency');
        $order_currency = (string)$order_xml->Payment->CurrencyCode;

        $products = array();

        $products_total = 0;
        $products_shipping = 0;
        $products_tax = 0;
        $products_shipping_tax = 0;
        $gift_wrap = 0;
        $gift_wrap_tax = 0;

        $product_count = 0;

        $amazonus_order_id = (string)$order_xml->AmazonOrderId;

        /* SKU => ORDER_ITEM_ID */
        $product_mapping = array();
        $product_gift_messages = array();

        foreach ($order_xml->Items->Item as $item) {

            $total_price = $this->currency->convert((double)$item->Totals->Price, $order_currency, $currency_to);
            $tax_total = (double)$item->Totals->Tax;

            if ($tax_total == 0 && $this->config->get('openbay_amazonus_order_tax') > 0) {
                $tax_total = (double)$item->Totals->Price * ($this->config->get('openbay_amazonus_order_tax') / 100) / (1 + $this->config->get('openbay_amazonus_order_tax') / 100);
            }

            $tax_total = $this->currency->convert($tax_total, $order_currency, $currency_to);

            $products_total += $total_price;
            $products_tax += $tax_total;

            $products_shipping += $this->currency->convert((double)$item->Totals->Shipping, $order_currency, $currency_to);

            $shipping_tax = (double)$item->Totals->ShippingTax;

            if ($shipping_tax == 0 && $this->config->get('openbay_amazonus_order_tax') > 0) {
                $shipping_tax = (double)$item->Totals->Shipping * ($this->config->get('openbay_amazonus_order_tax') / 100) / (1 + $this->config->get('openbay_amazonus_order_tax') / 100);
            }

            $products_shipping_tax += $this->currency->convert($shipping_tax, $order_currency, $currency_to);

            $gift_wrap += $this->currency->convert((double)$item->Totals->GiftWrap, $order_currency, $currency_to);

            $item_gift_wrap_tax = (double)$item->Totals->GiftWrapTax;

            if ($item_gift_wrap_tax == 0 && $this->config->get('openbay_amazonus_order_tax') > 0) {
                $item_gift_wrap_tax = (double)$item->Totals->GiftWrap * ($this->config->get('openbay_amazonus_order_tax') / 100) / (1 + $this->config->get('openbay_amazonus_order_tax') / 100);
            }

            $gift_wrap_tax += $this->currency->convert($item_gift_wrap_tax, $order_currency, $currency_to);

            $product_count += (int)$item->Ordered;

            if ((int)$item->Ordered == 0) {
                continue;
            }

            $product_id = $this->model_extension_openbay_amazonus_order->getProductId((string)$item->Sku);
            $product_var = $this->model_extension_openbay_amazonus_order->getProductVar((string)$item->Sku);

            $products[] = array(
                'product_id' => $product_id,
                'var' => $product_var,
                'sku' => (string)$item->Sku,
                'asin' => (string)$item->Asin,
                'order_item_id' => (string)$item->OrderItemId,
                'name' => (string)$item->Title,
                'model' => (string)$item->Sku,
                'quantity' => (int)$item->Ordered,
                'price' => sprintf('%.4f', ($total_price - $tax_total) / (int)$item->Ordered),
                'total' => sprintf('%.4f', $total_price - $tax_total),
                'tax' => $tax_total / (int)$item->Ordered,
                'reward' => '0',
                'option' => $this->model_extension_openbay_amazonus_order->getProductOptionsByVar($product_var),
                'download' => array(),
            );

            $product_mapping[(string)$item->Sku] = (string)$item->OrderItemId;

            if ($item->GiftMessage != '') {
                $product_gift_messages[] = (string)$item->Title . ' : ' . (string)$item->GiftMessage;
            }
        }

        $order_comment = '';
        if (count($product_gift_messages) > 0) {
            $order_comment = $this->language->get('text_gift_message') . '<br />' . implode('<br />', $product_gift_messages);
        }

        $total = sprintf('%.4f', $this->currency->convert((double)$order_xml->Payment->Amount, $order_currency, $currency_to));

        $address_line_2 = (string)$order_xml->Shipping->AddressLine2;
        if ((string)$order_xml->Shipping->AddressLine3 != '') {
            $address_line_2 .= ', ' . (string)$order_xml->Shipping->AddressLine3;
        }

        $customer_info = $this->db->query("SELECT `customer_id` FROM " . DB_PREFIX . "customer WHERE email = '" . $this->db->escape((string)$order_xml->Payment->Email) . "'")->row;
        $customer_id = '0';

        if(isset($customer_info['customer_id'])) {
            $customer_id = $customer_info['customer_id'];
        } else {
            /* Add a new customer */
            $customer_data = array(
                'firstname' => (string)$order_xml->Shipping->Name,
                'lastname' => '',
                'email' => (string)$order_xml->Payment->Email,
                'telephone' => (string)$order_xml->Shipping->Phone,
                'fax' => '',
                'newsletter' => '0',
                'customer_group_id' => $this->config->get('openbay_amazonus_order_customer_group'),
                'password' => '',
                'status' => '0',
            );

            $this->db->query("
                INSERT INTO " . DB_PREFIX . "customer
                SET firstname = '" . $this->db->escape($customer_data['firstname']) . "',
                    lastname = '" . $this->db->escape($customer_data['lastname']) . "',
                    email = '" . $this->db->escape($customer_data['email']) . "',
                    telephone = '" . $this->db->escape($customer_data['telephone']) . "',
                    newsletter = '" . (int)$customer_data['newsletter'] . "',
                    customer_group_id = '" . (int)$customer_data['customer_group_id'] . "',
                    password = '',
                    status = '" . (int)$customer_data['status'] . "',
                    date_added = NOW()");

            $customer_id = $this->db->getLastId();
        }

        $shipping_first_name = (string)$order_xml->Shipping->FirstName;
        $shipping_last_name = (string)$order_xml->Shipping->LastName;

        if (empty($shipping_first_name) || empty($shipping_last_name)) {
            $shipping_first_name = (string)$order_xml->Shipping->Name;
            $shipping_last_name = '';
        }

        $order = array(
            'invoice_prefix' => $this->config->get('config_invoice_prefix'),
            'store_id' => $this->config->get('config_store_id'),
            'store_name' => $this->config->get('config_name') . ' / Amazon US',
            'store_url' => $this->config->get('config_url'),
            'customer_id' => (int)$customer_id,
            'customer_group_id' => $this->config->get('openbay_amazonus_order_customer_group'),
            'firstname' => $shipping_first_name,
            'lastname' => $shipping_last_name,
            'email' => (string)$order_xml->Payment->Email,
            'telephone' => (string)$order_xml->Shipping->Phone,
            'shipping_firstname' => $shipping_first_name,
            'shipping_lastname' => $shipping_last_name,
            'shipping_company' => '',
            'shipping_address_1' => (string)$order_xml->Shipping->AddressLine1,
            'shipping_address_2' => $address_line_2,
            'shipping_city' => (string)$order_xml->Shipping->City,
            'shipping_postcode' => (string)$order_xml->Shipping->PostCode,
            'shipping_country' => $this->model_extension_openbay_amazonus_order->getCountryName((string)$order_xml->Shipping->CountryCode),
            'shipping_country_id' => $this->model_extension_openbay_amazonus_order->getCountryId((string)$order_xml->Shipping->CountryCode),
            'shipping_zone' => (string)$order_xml->Shipping->State,
            'shipping_zone_id' => $this->model_extension_openbay_amazonus_order->getZoneId((string)$order_xml->Shipping->State),
            'shipping_address_format' => '',
            'shipping_method' => (string)$order_xml->Shipping->Type,
            'shipping_code' => 'amazonus.' . (string)$order_xml->Shipping->Type,
            'payment_firstname' => $shipping_first_name,
            'payment_lastname' => $shipping_last_name,
            'payment_company' => '',
            'payment_address_1' => (string)$order_xml->Shipping->AddressLine1,
            'payment_address_2' => $address_line_2,
            'payment_city' => (string)$order_xml->Shipping->City,
            'payment_postcode' => (string)$order_xml->Shipping->PostCode,
            'payment_country' => $this->model_extension_openbay_amazonus_order->getCountryName((string)$order_xml->Shipping->CountryCode),
            'payment_country_id' => $this->model_extension_openbay_amazonus_order->getCountryId((string)$order_xml->Shipping->CountryCode),
            'payment_zone' => (string)$order_xml->Shipping->State,
            'payment_zone_id' => $this->model_extension_openbay_amazonus_order->getZoneId((string)$order_xml->Shipping->State),
            'payment_address_format' => '',
            'payment_method' => $this->language->get('text_paid_amazon'),
            'payment_code' => 'amazonus.amazonus',
            'payment_company_id' => 0,
            'payment_tax_id' => 0,
            'comment' => $order_comment,
            'total' => $total,
            'affiliate_id' => '0',
            'commission' => '0.00',
            'language_id' => (int)$this->config->get('config_language_id'),
            'currency_id' => $this->currency->getId($order_currency),
            'currency_code' => (string)$order_currency,
            'currency_value' => $this->currency->getValue($order_currency),
            'ip' => '',
            'forwarded_ip' => '',
            'user_agent' => 'OpenBay Pro for Amazon US',
            'accept_language' => '',
            'products' => $products,
            'vouchers' => array(),
            'marketing_id' => 0,
            'tracking' => 0,
            'totals' => array(
                array(
                    'code' => 'sub_total',
                    'title' => $this->language->get('text_total_sub'),
                    'value' => sprintf('%.4f', $products_total),
                    'sort_order' => '1',
                ),
                array(
                    'code' => 'shipping',
                    'title' => $this->language->get('text_total_shipping'),
                    'value' => sprintf('%.4f', $products_shipping),
                    'sort_order' => '3',
                ),
                array(
                    'code' => 'tax',
                    'title' => $this->language->get('text_tax'),
                    'value' => sprintf('%.4f', $products_tax),
                    'sort_order' => '4',
                ),
                array(
                    'code' => 'shipping_tax',
                    'title' => $this->language->get('text_total_shipping_tax'),
                    'value' => sprintf('%.4f', $products_shipping_tax),
                    'sort_order' => '6',
                ),
                array(
                    'code' => 'gift_wrap',
                    'title' => $this->language->get('text_total_giftwrap'),
                    'value' => sprintf('%.4f', $gift_wrap),
                    'sort_order' => '2',
                ),
                array(
                    'code' => 'gift_wrap_tax',
                    'title' => $this->language->get('text_total_giftwrap_tax'),
                    'value' => sprintf('%.4f', $gift_wrap_tax),
                    'sort_order' => '5',
                ),
                array(
                    'code' => 'total',
                    'title' => $this->language->get('text_total'),
                    'value' => sprintf('%.4f', $total),
                    'sort_order' => '7',
                ),
            ),
        );

        $order_id = $this->model_checkout_order->addOrder($order);

        $this->model_extension_openbay_amazonus_order->updateOrderStatus($order_id, $order_status);
        $this->model_extension_openbay_amazonus_order->addAmazonusOrder($order_id, $amazonus_order_id);
        $this->model_extension_openbay_amazonus_order->addAmazonusOrderProducts($order_id, $product_mapping);

        foreach($products as $product) {
            if($product['product_id'] != 0) {
                $this->model_extension_openbay_amazonus_order->decreaseProductQuantity($product['product_id'], $product['quantity'], $product['var']);
            }
        }

        $logger->write('Order ' . $amazonus_order_id . ' was added to the database (ID: ' . $order_id . ')');
        $logger->write("Finished processing the order");

        $this->model_extension_openbay_amazonus_order->acknowledgeOrder($order_id);

        //send an email to the administrator about the sale
        if ($this->config->get('openbay_amazonus_notify_admin') == 1){
            $this->openbay->newOrderAdminNotify($order_id, $order_status);
        }

        $this->event->trigger('model/checkout/order/addOrderHistory/after', array('model/checkout/order/addOrderHistory/after', array($order_id, $order_status)));

        $logger->write("Ok");
        $this->response->setOutput('Ok');
    }

    public function listing() {
        if ($this->config->get('openbay_amazonus_status') != '1') {
            return;
        }

        $this->load->model('extension/openbay/amazonus_listing');
        $this->load->model('extension/openbay/amazonus_product');

        $logger = new Log('amazonus_listing.log');
        $logger->write('amazonus/listing - started');

        $incoming_token = isset($this->request->post['token']) ? $this->request->post['token'] : '';

        if (!hash_equals($this->config->get('openbay_amazonus_token'), $incoming_token)) {
            $logger->write('amazonus/listing - Incorrect token: ' . $incoming_token);
            return;
        }

        $decrypted = $this->openbay->decrypt($this->request->post['data'], $this->openbay->amazonus->getEncryptionKey(), $this->openbay->amazonus->getEncryptionIv(), false);

        if (!$decrypted) {
            $logger->write('amazonus/order Failed to decrypt data');
            return;
        }

        $data = json_decode($decrypted, 1);

        $logger->write("Received data: " . print_r($data, 1));

        if ($data['status']) {
            $logger->write("Updating " . $data['product_id'] . ' as successful');
            $this->model_extension_openbay_amazonus_listing->listingSuccessful($data['product_id']);
            $this->model_extension_openbay_amazonus_product->linkProduct($data['sku'], $data['product_id']);
            $logger->write("Updated successfully");
        } else {
            $logger->write("Updating " . $data['product_id'] . ' as failed');
            $this->model_extension_openbay_amazonus_listing->listingFailed($data['product_id'], $data['messages']);
            $logger->write("Updated successfully");
        }
    }

    public function listingReport() {
        if ($this->config->get('openbay_amazonus_status') != '1') {
            return;
        }

        $this->load->model('extension/openbay/amazonus_product');

        $logger = new Log('amazonus.log');
        $logger->write('amazonus/listing_reports - started');

        $incoming_token = isset($this->request->post['token']) ? $this->request->post['token'] : '';

        if (!hash_equals($this->config->get('openbay_amazonus_token'), $incoming_token)) {
            $logger->write('amazonus/listing_reports - Incorrect token: ' . $incoming_token);
            return;
        }

        $decrypted = $this->openbay->decrypt($this->request->post['data'], $this->openbay->amazonus->getEncryptionKey(), $this->openbay->amazonus->getEncryptionIv(), false);

        if (!$decrypted) {
            $logger->write('amazonus/listing_reports - Failed to decrypt data');
            return;
        }

        $logger->write('Received Listing Report: ' . $decrypted);

        $request = json_decode($decrypted, 1);

        $data = array();

        foreach ($request['products'] as $product) {
            $data[] = array(
                'sku' => $product['sku'],
                'quantity' => $product['quantity'],
                'asin' => $product['asin'],
                'price' => $product['price'],
            );
        }

        if ($data) {
            $this->model_extension_openbay_amazonus_product->addListingReport($data);
        }

        $this->model_extension_openbay_amazonus_product->removeListingReportLock($request['marketplace']);

        $logger->write('amazonus/listing_reports - Finished');
    }

    public function product() {
        if ($this->config->get('openbay_amazonus_status') != '1') {
            $this->response->setOutput("disabled");
            return;
        }

        ob_start();

        $this->load->model('extension/openbay/amazonus_product');
        $logger = new Log('amazonus_product.log');

        $logger->write("AmazonusProduct/inbound: incoming data");

        $incoming_token = isset($this->request->post['token']) ? $this->request->post['token'] : '';

        if($incoming_token != $this->config->get('openbay_amazonus_token')) {
            $logger->write("Error - Incorrect token: " . $this->request->post['token']);
            ob_get_clean();
            $this->response->setOutput("tokens did not match");
            return;
        }

        $decrypted = $this->openbay->decrypt($this->request->post['data'], $this->openbay->amazonus->getEncryptionKey(), $this->openbay->amazonus->getEncryptionIv(), false);

        if(!$decrypted) {
            $logger->write("Error - Failed to decrypt received data.");
            ob_get_clean();
            $this->response->setOutput("failed to decrypt");
            return;
        }

        $decoded_data = (array)json_decode($decrypted);
        $logger->write("Received data: " . print_r($decoded_data, true));
        $status = $decoded_data['status'];

        if($status == "submit_error") {
            $message = 'Product was not submited to amazonus properly. Please try again or contact OpenBay.';
            $this->model_extension_openbay_amazonus_product->setSubmitError($decoded_data['insertion_id'], $message);
        } else {
            $status = (array)$status;
            if($status['successful'] == 1) {
                $this->model_extension_openbay_amazonus_product->setStatus($decoded_data['insertion_id'], 'ok');
                $insertion_product = $this->model_extension_openbay_amazonus_product->getProduct($decoded_data['insertion_id']);
                $this->model_extension_openbay_amazonus_product->linkProduct($insertion_product['sku'], $insertion_product['product_id'], $insertion_product['var']);
                $this->model_extension_openbay_amazonus_product->deleteErrors($decoded_data['insertion_id']);

                $quantity_data = array(
                    $insertion_product['sku'] => $this->model_extension_openbay_amazonus_product->getProductQuantity($insertion_product['product_id'], $insertion_product['var'])
                );
                $logger->write('Updating quantity with data: ' . print_r($quantity_data, true));
                $logger->write('Response: ' . print_r($this->openbay->amazonus->updateQuantities($quantity_data), true));
            } else {
                $msg = 'Product was not accepted by amazonus. Please try again or contact OpenBay.';
                $this->model_extension_openbay_amazonus_product->setSubmitError($decoded_data['insertion_id'], $msg);

                if(isset($decoded_data['error_details'])) {
                    foreach($decoded_data['error_details'] as $error) {
                        $error = (array)$error;
                        $error_data = array(
                            'sku' => $error['sku'],
                            'error_code' => $error['error_code'],
                            'message' => $error['message'],
                            'insertion_id' => $decoded_data['insertion_id']
                        );
                        $this->model_extension_openbay_amazonus_product->insertError($error_data);

                    }
                }
            }
        }

        $logger->write("Data processed successfully.");
        ob_get_clean();
        $this->response->setOutput("ok");
    }

    public function search() {
        if ($this->config->get('openbay_amazonus_status') != '1') {
            return;
        }

        $this->load->model('extension/openbay/amazonus_product');

        $logger = new Log('amazonus.log');
        $logger->write('amazonus/search - started');

        $incoming_token = isset($this->request->post['token']) ? $this->request->post['token'] : '';

        if (!hash_equals($this->config->get('openbay_amazonus_token'), $incoming_token)) {
            $logger->write('amazonus/search - Incorrect token: ' . $incoming_token);
            return;
        }

        $decrypted = $this->openbay->decrypt($this->request->post['data'], $this->openbay->amazonus->getEncryptionKey(), $this->openbay->amazonus->getEncryptionIv(), false);

        if (!$decrypted) {
            $logger->write('amazonus/search Failed to decrypt data');
            return;
        }

        $logger->write($decrypted);

        $json = json_decode($decrypted, 1);

        $this->model_extension_openbay_amazonus_product->updateSearch($json);
    }

    public function dev() {
        if ($this->config->get('openbay_amazonus_status') != '1') {
            $this->response->setOutput("error 001");
            return;
        }

        $incoming_token = isset($this->request->post['token']) ? $this->request->post['token'] : '';

        if ($incoming_token != $this->config->get('openbay_amazonus_token')) {
            $this->response->setOutput("error 002");
            return;
        }

        $decrypted = $this->openbay->decrypt($this->request->post['data'], $this->openbay->amazonus->getEncryptionKey(), $this->openbay->amazonus->getEncryptionIv(), false);

        if (!$decrypted) {
            $this->response->setOutput("error 003");
            return;
        }

        $data_xml = simplexml_load_string($decrypted);

        if(!isset($data_xml->action)) {
            $this->response->setOutput("error 004");
            return;
        }

        $action = trim((string)$data_xml->action);

        if ($action === "get_amazonus_product") {
            if(!isset($data_xml->product_id)) {
                $this->response->setOutput("error 005");
                return;
            }

            $product_id = trim((string)$data_xml->product_id);

            if ($product_id === "all") {
                $all_rows = $this->db->query("SELECT * FROM `" . DB_PREFIX . "amazonus_product`")->rows;

                $response = array();

                foreach ($all_rows as $row) {
                    unset($row['data']);
                    $response[] = $row;
                }

                $this->response->setOutput(print_r($response, true));

                return;
            } else {
                $response = $this->db->query("SELECT * FROM `" . DB_PREFIX . "amazonus_product` WHERE `product_id` = '" . (int)$product_id . "'")->rows;

                $this->response->setOutput(print_r($response, true));
                return;
            }
        } else {
            $this->response->setOutput("error 999");
            return;
        }
    }

    public function eventAddOrderHistory($route, $data) {
        $logger = new \Log('amazonus.log');
        $logger->write('eventAddOrderHistory Event fired: ' . $route);

        if (isset($data[0]) && !empty($data[0])) {
            $this->load->model('extension/openbay/amazonus_order');

            $logger->write('Order ID: ' . (int)$data[0]);

            $this->model_extension_openbay_amazonus_order->addOrderHistory((int)$data[0]);
        }
    }
}
