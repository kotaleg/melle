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

    public function formatImageLink($image)
    {
        return $this->getBase() . 'image/' . $image;
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
            } elseif ($this->request->get['route'] == 'checkout/success') {
                $class = 'checkout-success';
            } elseif ($this->request->get['route'] == 'checkout/checkout') {
                $class = 'checkout';
            } elseif ($this->request->get['route'] == 'checkout/simplecheckout') {
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
        $image = $this->getBase().'image/catalog/og-image.jpg';

        $this->load->model('tool/image');

        switch ($type) {
            case 'product':
                $this->load->model('catalog/product');
                if ($product_image = $this->model_catalog_product->getProductImage($this->getProductId())) {
                    $image =  $this->model_tool_image->resize($product_image, 350, 350);
                }
                break;
        }

        return $image;
    }

    public function getProductId()
    {
        if (isset($this->request->get['product_id'])) {
            return (int)$this->request->get['product_id'];
        }
        return null;
    }

    public function getCurrentCategoryName()
    {
        $this->load->model('catalog/category');

        $categoryPath = '';

        if (isset($this->request->get['path'])) {
            $categoryPath = $this->request->get['path'];
        }

        if (isset($this->request->get['categoryPath'])) {
            $categoryPath = $this->request->get['categoryPath'];
        }

        $parts = explode('_', $categoryPath);
        $category_id = (int) array_pop($parts);
        $category_info = $this->model_catalog_category->getCategory($category_id);

        if (isset($category_info['name'])) {
            return $category_info['name'];
        }

        return '';
    }

    public function formatMoney($price)
    {
        return number_format($price, 0, '.', ' ');
    }

    public function isLocal()
    {
        return defined('MELLELOCAL');
    }
}
