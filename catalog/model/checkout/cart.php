<?php
class ModelCheckoutCart extends Model
{
    public function getCart()
    {
        $state['count'] = $this->cart->countProducts();
        $state['products'] = array();

        if ($this->cart->hasProducts() || !empty($this->session->data['vouchers'])) {

            $this->load->model('extension/module/super_offers');
            $this->load->model('tool/image');

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

                $state['products'][] = array(
                    'cart_id'       => (int) $product['cart_id'],
                    'thumb'         => $image,
                    'name'          => $product['name'],
                    'model'         => $product['model'],
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

        // TOTAL
        $state['total'] = $this->cart->getTotal();

        return $state;
    }
}

