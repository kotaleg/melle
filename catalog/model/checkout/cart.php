<?php
class ModelCheckoutCart extends Model
{
    public function getCart()
    {
        $this->load->model('extension/pro_patch/url');

        $state['count'] = $this->cart->countProducts();
        $state['products'] = array();

        if ($this->cart->hasProducts() || !empty($this->session->data['vouchers'])) {

            $this->load->model('extension/module/super_offers');
            $this->load->model('tool/image');
            $this->load->model('catalog/manufacturer');

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
                    $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height'));
                }

                $option_data = array();

                foreach ($product['option'] as $option) {
                    $value = $option['value'];

                    $option_data[] = array(
                        'name'  => $option['name'],
                        'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
                    );
                }

                // Display prices
                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));

                    $price = round($unit_price, 0);
                    $total = round($unit_price * $product['quantity'], 0);
                } else {
                    $price = false;
                    $total = false;
                }

                // MANUFACTURER
                $manufacturer = '';
                $mf = $this->model_catalog_manufacturer->getManufacturer($product['manufacturer_id']);
                if ($mf) { $manufacturer = $mf['name']; }

                $state['products'][] = array(
                    'cart_id'       => (int) $product['cart_id'],
                    'product_id'    => $product['product_id'],
                    'thumb'         => $image,
                    'name'          => $product['name'],
                    'model'         => $product['model'],
                    'manufacturer'  => $manufacturer,
                    'option'        => $option_data,
                    'quantity'      => (int) $product['quantity'],
                    'max_quantity'  => isset($product['max_quantity']) ? (int) $product['max_quantity'] : 0,
                    'stock'         => $product['stock'] ? true : false,
                    'price'         => $price,
                    'total'         => $total,
                    'href'          => $this->model_extension_pro_patch_url->ajax('product/product', 'product_id=' . $product['product_id'])
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
            if (strcmp($total['code'], 'sub_total') === 0) {
                $state['total'] = $total['value'];
            }

            $state['totals'][] = array(
                'title' => $total['title'],
                'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
            );
        }

        return $state;
    }

    public function prepareFinalGTMData()
    {
        $data = array(
            'order_id' => $this->session->data['order_id'],
            'total' => 0,
            'tax' => 0,
            'shipping' => 0,
            'products' => array(),
        );

        $this->load->model('checkout/order');
        $this->load->model('catalog/product');
        $this->load->model('catalog/manufacturer');
        $this->load->model('extension/module/super_offers');

        $products = $this->model_checkout_order->getOrderProducts($this->session->data['order_id']);

        foreach ($products as $k => $p) {
            $extra = $this->model_catalog_product->getProduct($p['product_id']);

            $data['products'][] = array(
                'id' => $p['product_id'],
                'name' => $p['name'],
                'brand' => $extra['manufacturer'],
                'price' => $p['price'],
                'category' => '',
                'size' => '',
                'color' => '',
                'position' => $k,
                'quantity' => $p['quantity'],
            );
        }

        $data['products'] = json_encode($data['products']);

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

        foreach ($totals as $total) {
            if (strcmp($total['code'], 'sub_total') === 0) {
                $data['total'] = $total['value'];
            }

            if (in_array($total['code'], array('shipping'))) {
                $data['shipping'] = $total['value'];
            }
        }

        return $data;
    }
}

