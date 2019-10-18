<?php
class ControllerExtensionAdminProductFilter extends Controller {

    public function changeProductQuantity() {
        $json = [];

        if (!empty($this->request->get['product_id']) && isset($this->request->get['quantity']) && is_numeric($this->request->get['quantity'])) {
            $this->load->model('extension/admin_product_filter');

            $this->model_extension_admin_product_filter->changeProductQuantity($this->request->get['product_id'], $this->request->get['quantity']);

            $json['quantity'] = (int)$this->request->get['quantity'];
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function changeProductStatus() {
        if (!empty($this->request->get['product_id']) && isset($this->request->get['status'])) {
            $this->load->model('extension/admin_product_filter');

            $this->model_extension_admin_product_filter->changeProductStatus($this->request->get['product_id'], $this->request->get['status']);
        }
    }

    public function ShowHide() {
        $json = [];

        if (isset($this->request->post['key'])) {
            $this->load->model('setting/setting');

            $apf_settings = $this->model_setting_setting->getSetting('admin_product_filter');

            if (!empty($apf_settings['admin_product_filter']) && $this->request->post['key'] != 'column_left') {
                $column_key = 1;

                foreach ($apf_settings['admin_product_filter']['columns'] as $key => $value) {
                    if ($key === $this->request->post['key']) {
                        $apf_settings['admin_product_filter']['columns'][$key] = $value ? 0 : 1;

                        $json['column_key'] = $column_key;
                    }

                    $column_key++;
                }

                $this->model_setting_setting->editSetting('admin_product_filter', $apf_settings);

                $json['column']     = $this->request->post['key'];

                $json['success']    = true;
            }

            if (!empty($apf_settings['admin_product_filter']) && $this->request->post['key'] == 'column_left') {
                $apf_settings['admin_product_filter']['column_left'] = $json['column_left'] = $apf_settings['admin_product_filter']['column_left'] ? 0 : 1;

                $this->model_setting_setting->editSetting('admin_product_filter', $apf_settings);

                $json['success']    = true;
            }

            if (!empty($apf_settings['admin_product_filter']) && $this->request->post['key'] == 'column_right') {
                $apf_settings['admin_product_filter']['column_right'] = $json['column_right'] = $apf_settings['admin_product_filter']['column_right'] ? 0 : 1;

                $this->model_setting_setting->editSetting('admin_product_filter', $apf_settings);

                $json['success']    = true;
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function getProductData() {
        $json = [];

        if (isset($this->request->post['action']) && isset($this->request->post['product_id'])) {
            $this->load->model('extension/admin_product_filter');
            $this->load->model('catalog/product');

            $json['action'] = $action = $this->request->post['action'];
            $json['product_id'] = $product_id = $this->request->post['product_id'];
            $json['license_key'] = $license_key = $this->request->post['license_key'];

            // get Images
            if ($action == 'productImages') {
                $this->load->model('tool/image');

                $product_image = $this->model_extension_admin_product_filter->getProductImage($product_id, $license_key);

                if (is_file(DIR_IMAGE . $product_image)) {
                    $json['product_image'] = $product_image;
                    $json['product_thumb'] = $this->model_tool_image->resize($product_image, 60, 60);
                } else {
                    $json['product_image'] = '';
                    $json['product_thumb'] = $this->model_tool_image->resize('no_image.png', 60, 60);
                }

                $product_images = $this->model_catalog_product->getProductImages($product_id);

                $json['product_images'] = [];

                foreach ($product_images as $product_image) {
                    if (is_file(DIR_IMAGE . $product_image['image'])) {
                        $image = $product_image['image'];
                        $thumb = $product_image['image'];
                    } else {
                        $image = '';
                        $thumb = 'no_image.png';
                    }

                    $json['product_images'][] = [
                        'product_image_id'  => $product_image['product_image_id'],
                        'image'             => $image,
                        'thumb'             => $this->model_tool_image->resize($thumb, 60, 60),
                        'sort_order'        => $product_image['sort_order']
                    ];
                }
            }

            // get Name
            if ($action == 'productNames') {
                $this->load->model('localisation/language');

                $json['languages'] = $this->model_localisation_language->getLanguages();

                $names = $this->model_extension_admin_product_filter->getProductNames($product_id, $license_key);

                foreach ($names as $name) {
                    $json['names'][$name['language_id']] = $name;
                }
            }

            // get Model
            if ($action == 'productModel') {
                $json['model'] = trim($this->model_extension_admin_product_filter->getProductModel($product_id));
            }

            // get Sku
            if ($action == 'productSku') {
                $json['sku'] = trim($this->model_extension_admin_product_filter->getProductSku($product_id));
            }

            // get Manufacturer
            if ($action == 'getProductManufacturer') {
                $json['manufacturer_id'] = $this->model_extension_admin_product_filter->getProductManufacturerID($product_id, $license_key);
            }

            // get Product Category
            if ($action == 'getProductCategory') {
                $this->load->model('catalog/category');

                $categories = $this->model_catalog_product->getProductCategories($product_id);

                $json['product_category'] = [];

                foreach ($categories as $category_id) {
                    $category_info = $this->model_catalog_category->getCategory($category_id);

                    if ($category_info) {
                        $json['product_category'][] = [
                            'category_id' => $category_info['category_id'],
                            'name'        => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
                        ];
                    }
                }

                $seo_pro_config = $this->config->get('sla_seo_pro_status');

                if ($seo_pro_config) {
                    $json['main_category_id'] = $this->model_extension_admin_product_filter->getProductMainCategoryID($product_id);
                }

                if (!$seo_pro_config) {
                    $seo_pro_config = $this->config->get('config_seo_pro');
                    if ($seo_pro_config) {
                        $json['main_category_id'] = $this->model_extension_admin_product_filter->getProductMainCategoryID($product_id);
                    }
                }

                if (!$seo_pro_config) {
                    $seo_pro_config = $this->config->get('config_seo_url_type');

                    if ($seo_pro_config == 'seo_pro') {
                        $json['main_category_id'] = $this->model_extension_admin_product_filter->getProductMainCategoryID($product_id);
                    }
                }

                /*
                if ($this->config->get('sla_seo_pro_status')) {
                    $json['main_category_id'] = $this->model_extension_admin_product_filter->getProductMainCategoryID($product_id);
                }
                */
            }

            // get Price
            if ($action == 'getPrice') {
                $product_info = $this->model_catalog_product->getProduct($product_id);

                $json['price'] = $product_info['price'];
            }

            // get Price Special
            if ($action == 'getPriceSpecial') {
                $product_specials = $this->model_catalog_product->getProductSpecials($product_id);

                foreach ($product_specials as $product_special) {
                    if (($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time())) {
                        $json['price'] = $product_special['price'];
                        $json['product_special_id'] = $product_special['product_special_id'];

                        break;
                    }
                }
            }

            $json['success'] = true;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function editProductData() {
        $json = [];

        if (isset($this->request->post['action']) && isset($this->request->post['product_id'])) {
            $this->load->model('extension/admin_product_filter');
            $this->load->model('catalog/product');

            $json['action'] = $action = $this->request->post['action'];
            $json['product_id'] = $product_id = $this->request->post['product_id'];
            $json['license_key'] = $license_key = $this->request->post['license_key'];

            // edit Product Images
            if ($action == 'productImages') {
                $json['product_image'] = $product_image = $this->request->post['image'];

                $this->model_extension_admin_product_filter->editProductImage($product_id, $product_image, $license_key);

                if (isset($this->request->post['product_images'])) {
                    $json['product_images'] = $product_images = $this->request->post['product_images'];
                } else {
                    $product_images = [];
                }

                $this->model_extension_admin_product_filter->editProductImages($product_id, $product_images, $license_key);

                $this->load->model('tool/image');

                if (is_file(DIR_IMAGE . $product_image)) {
                    $json['product_image'] = $product_image;
                    $json['product_thumb'] = $this->model_tool_image->resize($product_image, 40, 40);
                } else {
                    $json['product_image'] = '';
                    $json['product_thumb'] = $this->model_tool_image->resize('no_image.png', 40, 40);
                }

                $json['product_images'] = [];

                array_multisort(array_column($product_images, 'sort_order'), SORT_ASC, $product_images);

                foreach ($product_images as $product_image) {
                    if (is_file(DIR_IMAGE . $product_image['image'])) {
                        $image = $product_image['image'];
                        $thumb = $product_image['image'];
                    } else {
                        $image = '';
                        $thumb = 'no_image.png';
                    }

                    $json['product_images'][] = [
                        'image'             => $image,
                        'thumb'             => $this->model_tool_image->resize($thumb, 20, 20),
                        'sort_order'        => $product_image['sort_order']
                    ];
                }
            }

            // edit Product Names
            if ($action == 'productNames') {
                $language_id = $this->config->get('config_language_id');

                foreach ($this->request->post['names'] as $name) {
                    if ($language_id == $name['language_id']) {
                        $json['name'] = trim($name['name']);
                    }

                    $this->model_extension_admin_product_filter->editProductNames($product_id, $name['language_id'], trim($name['name']), $license_key);
                }
            }

            // edit Product Model
            if ($action == 'productModel') {
                $json['model'] = $model = trim($this->request->post['model']);

                $this->model_extension_admin_product_filter->editProductModel($product_id, $model);
            }

            // edit Product Sku
            if ($action == 'productSku') {
                $json['sku'] = $sku = trim($this->request->post['sku']);

                $this->model_extension_admin_product_filter->editProductSku($product_id, $sku);
            }

            // edit Product Manufacturer
            if ($action == 'editProductManufacturer') {
                $json['manufacturer_id'] = $manufacturer_id = $this->request->post['manufacturer_id'];

                $json['manufacturer_name'] = $this->request->post['manufacturer_name'];

                $this->model_extension_admin_product_filter->editProductManufacturerID($product_id, $manufacturer_id, $license_key);
            }

            // edit Product Category
            if ($action == 'editProductCategory') {
                if (!isset($this->request->post['product_category'])) {
                    $this->request->post['product_category'] = [];
                }

                $json['product_category'] = $product_category = $this->request->post['product_category'];

                if (isset($this->request->post['main_category_id'])) {
                    $json['main_category_id'] = $main_category_id = $this->request->post['main_category_id'];
                } else {
                    $main_category_id = false;
                }

                $this->model_extension_admin_product_filter->editProductCategory($product_id, $product_category, $main_category_id, $license_key);

                $this->load->model('catalog/category');

                $categories = $this->model_catalog_product->getProductCategories($product_id);

                $json['product_category'] = [];

                foreach ($categories as $category_id) {
                    $category_info = $this->model_catalog_category->getCategory($category_id);

                    if ($category_info) {
                        $json['product_category'][] = [
                            'category_id' => $category_info['category_id'],
                            'name'        => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
                        ];
                    }
                }
            }

            // edit Price
            if ($action == 'editPrice') {
                $json['price'] = $price = $this->request->post['price'];

                $json['price_format'] = $this->currency->format($price, $this->config->get('config_currency'));

                $this->model_extension_admin_product_filter->editProductPrice($product_id, $price);
            }

            // edit Price Special
            if ($action == 'editPriceSpecial') {
                $json['price'] = $price = $this->request->post['price'];

                $json['price_format'] = $this->currency->format($price, $this->config->get('config_currency'));

                $this->model_extension_admin_product_filter->editProductPriceSpecial($product_id, $price, $this->request->post['product_special_id']);

                $product_specials = $this->model_catalog_product->getProductSpecials($product_id);

                foreach ($product_specials as $product_special) {
                    if (($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time())) {
                        $json['price'] = $product_special['price'];
                        $json['price_format'] = $this->currency->format($product_special['price'], $this->config->get('config_currency'));

                        break;
                    }
                }
            }

            // edit Quantity
            if ($action == 'editQuantity') {
                $json['quantity'] = $quantity = $this->request->post['quantity'];

                $this->model_extension_admin_product_filter->editProductQuantity($product_id, $quantity);
            }

            $json['success'] = true;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}