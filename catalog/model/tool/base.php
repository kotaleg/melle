<?php
class ModelToolBase extends Model
{
    public function getBase()
    {
        if ($this->request->server['HTTPS']) {
            return $this->config->get('config_ssl');
        } else {
            return $this->config->get('config_url');
        }
    }

    public function getPageType()
    {
        $pagetype = '';

        if (isset($this->request->get['route'])) {

            if ($this->request->get['route'] == 'common/home') {
                $class = 'home';
            } elseif (isset($this->request->get['product_id'])) {
                $class = 'product';
            } elseif (isset($this->request->get['path'])) {
                $class = 'category';
            } elseif (isset($this->request->get['manufacturer_id'])) {
                $class = 'manufacturer';
            } elseif ($this->request->get['route'] == 'product/search') {
                $class = 'search';
            } elseif ($this->request->get['route'] == 'checkout/cart') {
                $class = 'cart';
            } elseif ($this->request->get['route'] == 'checkout/checkout') {
                $class = 'checkout';
            } elseif ($this->request->get['route'] == 'account/login') {
                $class = 'login';
            } elseif ($this->request->get['route'] == 'account/register') {
                $class = 'register';
            } elseif ($this->request->get['route'] == 'account/forgotten') {
                $class = 'forgotten';
            } else {
                $class = '';
            }

            $pagetype = $class;
        } else {
            $pagetype = 'home';
        }

        return $pagetype;
    }

    public function getOgImage($size = 350)
    {
        $type = $this->getPageType();
        $image = '';

        switch ($type) {

            default:
                $image = 'image/catalog/og-image.jpg';
                break;
        }

        return $this->getBase().$image;
    }

    public function getProductId()
    {
        if (isset($this->request->get['product_id'])) {
            return (int)$this->request->get['product_id'];
        }
        return null;
    }
}
