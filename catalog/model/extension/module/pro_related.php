<?php
/*
 *  location: catalog/model
 */
class ModelExtensionModuleProRelated extends Model
{
    private $codename = 'pro_related';

    private function getCartProducts()
    {
        return array_map(function($i) {
            if (isset($i['product_id'])) {
                return $i['product_id'];
            }
        }, $this->cart->getProducts());
    }

    public function prepareCartProducts($max = 4)
    {
        $products = array();
        $results = $this->getCartProducts();

        $this->load->model('catalog/product');
        $this->load->model('extension/module/pro_znachek');
        $this->load->model('extension/module/super_offers');
        $this->load->model('tool/image');

        $related = array();

        foreach ($results as $pid) {
            foreach ($this->model_catalog_product->getProductRelated($pid, true) as $v) {
                $related[$v] = $v;
            }
        }

        shuffle($related);
        $related = array_slice($related, 0, $max);

        foreach ($related as $pid) {
            $result = $this->model_catalog_product->getProduct($pid);
            if (!$result) { continue; }

            if ($result['image']) {
                $image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
            } else {
                $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
            }

            if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                $price = round($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), 0);
            } else {
                $price = false;
            }

            if ((float)$result['special']) {
                $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $special = false;
            }

            if ($this->config->get('config_tax')) {
                $tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
            } else {
                $tax = false;
            }

            if ($this->config->get('config_review_status')) {
                $rating = (int)$result['rating'];
            } else {
                $rating = false;
            }

            $products[] = array(
                'product_id'  => $result['product_id'],
                'thumb'       => $image,
                'name'        => $result['name'],
                'h1'          => $result['h1'],
                'znachek_class' => $this->model_extension_module_pro_znachek->getZnachekClass($result['znachek']),
                'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                'price'       => $this->model_extension_module_super_offers->getLowestPrice($result['product_id']),
                'special'     => $special,
                'tax'         => $tax,
                'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
                'rating'      => $rating,
                'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'], true)
            );
        }

        return $products;
    }
}