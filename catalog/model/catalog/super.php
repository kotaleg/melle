<?php
class ModelCatalogSuper extends Model
{
    const MAX_VALUE = -999;
    const MIN_VALUE = 999999999;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/url');
        $this->load->model('catalog/product');
        $this->load->model('catalog/category');
        $this->load->model('tool/image');
    }

    public function getProducts($filter_data = array())
    {
        $result = array(
            'products' => array(),
            'product_total' => 0,
        );
        $filter_data = $this->prepareInitialData($filter_data);

        $result['product_total'] = $this->model_catalog_product->getTotalProducts($filter_data);
        $products = $this->model_catalog_product->getProducts($filter_data);

        $this->load->model('extension/pro_patch/url');
        $this->load->model('extension/module/pro_znachek');
        $this->load->model('extension/module/super_offers');

        foreach ($products as $p) {

            if ($p['image']) {
                $image = $this->model_tool_image->resize($p['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
            } else {
                $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
            }

            $d_ = array(
                'product_id' => $p['product_id'],
                'href' => $this->model_extension_pro_patch_url->ajax('product/product', 'product_id=' . (int)$p['product_id']),
                'name' => $p['name'],
                'h1' => $p['h1'],
                'manufacturer' => $p['manufacturer'],
                'image' => $image,
                'special' => 0,
                'rating' => $p['rating'],
                'reviews_count' => $p['reviews'],

                'quantity' => 1,
                'default_values' => $this->model_extension_module_super_offers->getDefaultValues($p['product_id']),
                // 'is_options_for_product' => (bool)$this->model_extension_module_super_offers->isOptionsForProduct($p['product_id']),
                // 'options' => $this->model_extension_module_super_offers->getOptions($p['product_id']),
            );

            // $d['combinations_for_options'] = $this->model_extension_module_super_offers->getCombinationsForOptions(
            //     $p['product_id'], $d_['options']);

            $attribute_groups = $this->model_catalog_product->getProductAttributes($p['product_id']);

            $d_['material'] = false;
            $d_['den'] = false;

            foreach ($attribute_groups as $group) {
                if (strcmp(trim($group['name']), 'Атрибуты') === 0) {
                    foreach ($group['attribute'] as $attr) {
                        if (strcmp(trim($attr['name']), 'Матерал') === 0) {
                            $d_['material'] = $attr['text'];
                        }
                        if (strcmp(trim($attr['name']), 'Ден') === 0) {
                            $d_['den'] = $attr['text'];
                        }
                    }
                }
            }

            $d_['znachek'] = $this->model_extension_module_pro_znachek->getZnachek($p['znachek']);
            $d_['znachek_class'] = $this->model_extension_module_pro_znachek->getZnachekClass($p['znachek']);

            $result['products'][] = $d_;
        }

        return $result;
    }

    public function getFilterValues($filter_data = array())
    {
        $filter_data = $this->prepareInitialData($filter_data);
        $result = array(
            'min_den' => null,
            'max_den' => null,
            'min_price' => null,
            'max_price' => null,
            'hit' => null,
            'new' => null,
            'act' => null,
            'material' => null,
            'color' => null,
            'size' => null,
            'manufacturers' => null,

            'category_id' => '',
            'search' => null,

            'page' => 1,
            'sort' => 'p.sort_order',
            'order' => 'ASC',
            'limit' => $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'),
        );

        if (isset($filter_data['filter_category_id'])) {
            $result['category_id'] = $filter_data['filter_category_id'];
        }

        if (isset($filter_data['page'])) {
            $result['page'] = (int)$filter_data['page'];
        }

        if (isset($filter_data['limit'])) {
            $result['limit'] = (int)$filter_data['limit'];
        }

        return $result;
    }

    public function prepareInitialData($filter_data = array())
    {
        /* FROM GET PARAMS START */
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'p.sort_order';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        if (isset($this->request->get['limit'])) {
            $limit = (int)$this->request->get['limit'];
        } else {
            $limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
        }

        if (isset($this->request->get['search'])) {
            $search = $this->request->get['search'];
        } else {
            $search = null;
        }

        if (isset($this->request->get['path'])) {
            $parts = explode('_', (string)$this->request->get['path']);
            $category_id = (int)array_pop($parts);
        } else {
            $category_id = 0;
        }
        /* FROM GET PARAMS END */

        $min_den = null;
        $max_den = null;
        $min_price = null;
        $max_price = null;

        $hit = false;
        $new = false;
        $act = false;

        $material = null;
        $color = null;
        $size = null;

        $manufacturers = null;

        /* FROM FILTER START */
        if (isset($filter_data['page'])) {
            $page = (int)$filter_data['page'];
        }
        /* FROM FILTER END */

        $prepared = array(
            'filter_category_id'  => $category_id,
            'filter_sub_category' => true,
            'sort'                => $sort,
            'order'               => $order,
            'start'               => ($page - 1) * $limit,
            'page'                => $page,
            'limit'               => $limit,
        );

        if ($search !== null) { $prepared['filter_name'] = (string)$search; }
        if ($min_den !== null) { $prepared['min_den'] = (float)$min_den; }
        if ($max_den !== null) { $prepared['max_den'] = (float)$max_den; }
        if ($min_price !== null) { $prepared['min_price'] = (float)$min_price; }
        if ($max_price !== null) { $prepared['max_price'] = (float)$max_price; }
        if ($hit !== false) { $prepared['hit'] = (bool)$hit; }
        if ($new !== false) { $prepared['new'] = (bool)$new; }
        if ($act !== false) { $prepared['act'] = (bool)$act; }
        if ($material !== null) { $prepared['material'] = $material; }
        if ($color !== null) { $prepared['color'] = $color; }
        if ($size !== null) { $prepared['size'] = $size; }
        if ($manufacturers !== null && is_array($manufacturers)) {
            $prepared['manufacturers'] = array_filter($manufacturers); }

        $den_id = $this->getDenId();
        if ($den_id !== null) {
            $prepared['den_id'] = $den_id;
        }

        return $prepared;
    }

    private function getDenId()
    {
        $q = $this->db->query("SELECT `attribute_id` FROM " . DB_PREFIX . "attribute_description
            WHERE LCASE(`name`) = 'ден'");

        if (isset($q->row['attribute_id'])) {
            return (int)$q->row['attribute_id'];
        }

        return null;
    }


}