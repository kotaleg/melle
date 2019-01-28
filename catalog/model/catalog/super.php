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

        $result['product_total'] = (int) $this->model_catalog_product->getTotalProducts($filter_data);
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

            if (isset($filter_data['path']) && !empty($filter_data['path'])) {
                $href = $this->model_extension_pro_patch_url->ajax('product/product', 'path=' . $filter_data['path'] .'&product_id=' . (int)$p['product_id']);
            } else {
                $href = $this->model_extension_pro_patch_url->ajax('product/product', 'product_id=' . (int)$p['product_id']);
            }

            // echo "<pre>"; print_r(''); echo "</pre>";exit;

            $d_ = array(
                'product_id' => $p['product_id'],
                'href' => $href,
                'name' => $p['name'],
                'h1' => $p['h1'],
                'manufacturer' => $p['manufacturer'],
                'image' => $image,
                'special' => 0,
                'rating' => $p['rating'],
                'reviews_count' => $p['reviews'],

                'quantity' => 1,
                'default_values' => $this->model_extension_module_super_offers->getDefaultValues($p['product_id'], $p),
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
        $product_total = $this->model_catalog_product->getTotalProducts($filter_data, true);

        $result = array(
            'min_den' => 0,
            'max_den' => 1000,
            'min_price' => 0,
            'max_price' => 10000,
            'hit' => false,
            'neww' => false,
            'act' => false,
            'material' => '',
            'color' => '',
            'size' => '',
            'manufacturers' => [],

            'category_id' => '',
            'search' => null,

            'page' => 1,
            'sort' => 'pd.name',
            'all_sorts' => array('pd.name', 'offers.price'),
            'order' => 'ASC',
            'limit' => $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'),
            'path' => '',
        );

        if ($product_total) {
            $result['min_den'] = (int) $product_total['min_den'];
            $result['max_den'] = (int) $product_total['max_den'];
            $result['min_price'] = (int) $product_total['min_price'];
            $result['max_price'] = (int) $product_total['max_price'];
        }

        if (isset($filter_data['filter_category_id'])) {
            $result['category_id'] = $filter_data['filter_category_id'];
        }
        if (isset($filter_data['page'])) {
            $result['page'] = (int)$filter_data['page'];
        }
        if (isset($filter_data['path']) && !empty($filter_data['path'])) {
            $result['path'] = (string)$filter_data['path'];
        }
        if (isset($filter_data['sort'])) {
            $result['sort'] = (string)$filter_data['sort'];
        }
        if (isset($filter_data['order'])) {
            $result['order'] = (string)$filter_data['order'];
        }
        if (isset($filter_data['limit'])) {
            $result['limit'] = (int)$filter_data['limit'];
        }
        if (isset($filter_data['filter_name'])) {
            $result['search'] = $filter_data['filter_name'];
        }
        if (isset($filter_data['act'])) {
            $result['act'] = $filter_data['act'];
        }
        if (isset($filter_data['neww'])) {
            $result['neww'] = $filter_data['neww'];
        }
        if (isset($filter_data['hit'])) {
            $result['hit'] = $filter_data['hit'];
        }
        if (isset($filter_data['min_den'])) {
            $result['min_den'] = $filter_data['min_den'];
        }
        if (isset($filter_data['max_den'])) {
            $result['max_den'] = $filter_data['max_den'];
        }
        if (isset($filter_data['min_price'])) {
            $result['min_price'] = $filter_data['min_price'];
        }
        if (isset($filter_data['max_price'])) {
            $result['max_price'] = $filter_data['max_price'];
        }
        if (isset($filter_data['manufacturers'])
        && is_array($filter_data['manufacturers'])) {
            $manu_ = $filter_data['manufacturers'];
        } else { $manu_ = array(); }

        if (isset($filter_data['material'])
        && is_array($filter_data['material'])) {
            $material = $filter_data['material'];
        }


        // ALL MANUFACTURERS
        $manufacturers = $this->model_catalog_product->getManufacturersForFilter($filter_data);
        $manufacturers = array_filter($manufacturers, function($v) {
            if ($v['value']) { return true; }
        });
        $result['manufacturers'] = array_map(function($v) use ($manu_) {
            $v['checked'] = false;
            if (in_array($v['value'], $manu_)) { $v['checked'] = true; }
            return $v;
        }, $manufacturers);

        // ALL MATERIALS
        // $result['all_materials'] = array();
        // $materials = $this->model_catalog_product->getMaterialsForFilter($filter_data);
        // $materials_check = array();
        // $materials = array_filter($materials, function($v) {
        //     if ($v['value']) { return true; }
        // });
        // foreach ($materials as $m) {
        //     if (!in_array(trim($m['value']), $materials_check)) {
        //         $materials_check[] = trim($m['value']);
        //         $result['all_materials'][] = $m;

        //         if (isset($material) && trim($material) == trim($m['value'])) {
        //             $result['material'] = array(
        //                 'label' => $material,
        //                 'value' => $value,
        //             );
        //         }
        //     }
        // }

        return $result;
    }

    public function prepareInitialData($filter_data = array())
    {
        /* FROM GET PARAMS START */
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'pd.name';
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
        if (isset($this->request->get['path']) && !empty($this->request->get['path'])) {
            $path = $this->request->get['path'];
        } else {
            $path = '';
        }
        /* FROM GET PARAMS END */

        $min_den = null;
        $max_den = null;
        $min_price = null;
        $max_price = null;

        $hit = false;
        $neww = false;
        $act = false;

        $material = null;
        $color = null;
        $size = null;

        $manufacturers = [];

        /* FROM FILTER START */
        if (isset($filter_data['page'])) {
            $page = (int)$filter_data['page'];
        }
        if (isset($filter_data['category_id'])) {
            $category_id = (int)$filter_data['category_id'];
        }
        if (isset($filter_data['min_den']) && (float)$filter_data['min_den'] > 0) {
            $min_den = (float)$filter_data['min_den'];
        }
        if (isset($filter_data['max_den']) && (float)$filter_data['max_den'] > 0) {
            $max_den = (float)$filter_data['max_den'];
        }
        if (isset($filter_data['min_price']) && (float)$filter_data['min_price'] > 0) {
            $min_price = (float)$filter_data['min_price'];
        }
        if (isset($filter_data['max_price']) && (float)$filter_data['max_price'] > 0) {
            $max_price = (float)$filter_data['max_price'];
        }
        if (isset($filter_data['hit'])) {
            $hit = (bool)$filter_data['hit'];
        }
        if (isset($filter_data['neww'])) {
            $neww = (bool)$filter_data['neww'];
        }
        if (isset($filter_data['act'])) {
            $act = (bool)$filter_data['act'];
        }
        if (isset($filter_data['material'])
        && is_array($filter_data['material'])) {
            $material = $filter_data['material']['value'];
        }
        if (isset($filter_data['color'])
        && is_array($filter_data['color'])) {
            $color = $filter_data['color']['value'];
        }
        if (isset($filter_data['size'])
        && is_array($filter_data['size'])) {
            $size = $filter_data['size']['value'];
        }
        if (isset($filter_data['manufacturers'])) {
            $manufacturers = $filter_data['manufacturers'];
            $manufacturers = array_map(function($v) {
                if ($v['checked']) { return $v['value']; }
            }, $manufacturers);
            $manufacturers = array_filter($manufacturers);
        }

        if (isset($filter_data['search'])) {
            $search = (string)$filter_data['search'];
        }
        if (isset($filter_data['path']) && !empty($filter_data['path'])) {
            $path = (string)$filter_data['path'];
        }
        if (isset($filter_data['order']) && !empty($filter_data['order'])) {
            $order = (string)$filter_data['order'];
        }
        if (isset($filter_data['sort']) && !empty($filter_data['sort'])) {
            $sort = (string)$filter_data['sort'];
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
        if ($path !== null) { $prepared['path'] = (string)$path; }
        if ($min_den !== null) { $prepared['min_den'] = (float)$min_den; }
        if ($max_den !== null) { $prepared['max_den'] = (float)$max_den; }
        if ($min_price !== null) { $prepared['min_price'] = (float)$min_price; }
        if ($max_price !== null) { $prepared['max_price'] = (float)$max_price; }
        if ($hit !== false) { $prepared['hit'] = (bool)$hit; }
        if ($neww !== false) { $prepared['neww'] = (bool)$neww; }
        if ($act !== false) { $prepared['act'] = (bool)$act; }
        if ($material !== null) { $prepared['material'] = $material; }
        if ($color !== null) { $prepared['color'] = $color; }
        if ($size !== null) { $prepared['size'] = $size; }
        if (is_array($manufacturers) && !empty($manufacturers)) {
            $prepared['manufacturers'] = array_filter($manufacturers);
        }

        $den_id = $this->getDenId();
        if ($den_id !== null) {
            $prepared['den_id'] = $den_id;
        }

        $material_id = $this->getMaterialId();
        if ($material_id !== null) {
            $prepared['material_id'] = $material_id;
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

    private function getMaterialId()
    {
        $q = $this->db->query("SELECT `attribute_id` FROM " . DB_PREFIX . "attribute_description
            WHERE LCASE(`name`) = 'материал'");

        if (isset($q->row['attribute_id'])) {
            return (int)$q->row['attribute_id'];
        }

        return null;
    }

    public function prepareSliderOptions()
    {
        $filter_data = $this->prepareInitialData(array(
            'page' => 1,
            'search' => null,
            'category_id' => 0,
        ));
        $product_total = $this->model_catalog_product->getTotalProducts($filter_data, true);

        return array(
            'den' => $this->getSliderOptions($product_total['min_den'], $product_total['max_den']),
            'price' => $this->getSliderOptions($product_total['min_price'], $product_total['max_price']),
        );
    }

    private function getSliderOptions($min, $max)
    {
        return array(
            'min' => (int) $min,
            'max' => (int) $max,
            'disabled' => false,
            'show' => true,
            'tooltip' => 'newer',

            'bgStyle' => array(
                'border' => '1px solid #c5c5c5',
                'background' => '#fff',
                'height' => '12.5px',
                'border-radius' => 0,
            ),

            'sliderStyle' => array(
                array(
                    'backgroundColor' => '#2b2a29',
                    'width' => '20px',
                    'height' => '20px',
                    'border-radius' => 0,
                ),
                array(
                    'backgroundColor'   => '#2b2a29',
                    'width' => '20px',
                    'height' => '20px',
                    'border-radius' => 0,
                ),
            ),

            'processStyle' => array(
                'background' => '#e9e9e9',
            ),
        );
    }

}