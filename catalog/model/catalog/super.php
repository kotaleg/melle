<?php
class ModelCatalogSuper extends Model
{
    const MAX_VALUE = -999;
    const MIN_VALUE = 999999999;

    private $timing = array();

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/url');
        $this->load->model('catalog/product');
        $this->load->model('catalog/category');
        $this->load->model('tool/image');
        $this->load->model('tool/base');
    }

    public function getProducts($filter_data = array())
    {
        $time_start = microtime(true);

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
        $this->load->model('extension/total/pro_discount');


        foreach ($products as $p) {

            if ($p['image'] && is_file(DIR_IMAGE . $p['image'])) {
                $image = $this->model_tool_image->resize($p['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
            } else {
                continue;
            }

            if (isset($filter_data['path']) && !empty($filter_data['path'])) {
                $href = $this->model_extension_pro_patch_url->ajax('product/product', 'path=' . $filter_data['path'] .'&product_id=' . (int)$p['product_id']);
            } else {
                $href = $this->model_extension_pro_patch_url->ajax('product/product', 'product_id=' . (int)$p['product_id']);
            }

            $routerLink = "/product/{$p['product_id']}";
            if ($this->config->get('config_seo_url')) {
                $routerLink = '/'. str_replace(HTTPS_SERVER, '', $href);
            }

            $httpQueryData = ['product_id' => $p['product_id']];
            if (isset($filter_data['path']) && !empty($filter_data['path'])) {
                $httpQueryData['categoryPath'] = (string) $filter_data['path'];
            }
            $httpQuery = http_build_query($httpQueryData);

            $routerLink .= "?{$httpQuery}";

            $d_ = array(
                'product_id' => $p['product_id'],
                'href' => $href,
                'router_link' => $routerLink,
                'name' => $p['h1'],
                'manufacturer' => $p['manufacturer'],
                'image' => $image,
                'special' => 0,
                'rating' => $p['rating'],
                'reviews_count' => $p['reviews'],
                'special_text' => false,
                'zvezdochka' => false,

                'quantity' => 1,
                'default_values' => $this->model_extension_module_super_offers->getDefaultValues($p['product_id'], $p),
            );

            $d_['in_stock'] = ((int) $d_['default_values']['max_quantity'] > 0) ? true : false;

            $attribute_groups = $this->model_catalog_product->getProductAttributes($p['product_id']);

            $d_['material'] = false;
            $d_['den'] = false;

            foreach ($attribute_groups as $group) {
                if (strcmp(trim($group['name']), 'Атрибуты') === 0) {
                    foreach ($group['attribute'] as $attr) {
                        if (strcmp(trim($attr['name']), 'Материал') === 0) {
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

            // SPECIAL TEXT
            $d_['special_text'] = $this->model_extension_total_pro_discount->getSpecialText($p['product_id'], true);
            if (strstr($d_['special_text'], '*')) { $d_['zvezdochka'] = true; }

            $result['products'][] = $d_;
        }

        $time_end = microtime(true);
        $this->timing[] = array(
            'PRODUCTS' => round(($time_end - $time_start) * 1000),
        );

        return $result;
    }

    public function getFilterValues($filter_data = array())
    {
        $time_start = microtime(true);

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
            'discount_id' => '',
            'search' => null,

            'page' => 1,
            'sort' => array('label' => 'Наименованию', 'value' => 'pd.name'),
            'order' => 'ASC',
            'limit' => $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'),
            'path' => '',
        );

        $result['all_sorts'] = array(
            0 => array('label' => 'Наименованию', 'value' => 'pd.name'),
            1 => array('label' => 'По цене', 'value' => 'offers.price'),
        );

        if (isset($filter_data['sort']) && !empty($filter_data['sort'])) {
            foreach ($result['all_sorts'] as $k => $v) {
                if (strcmp($filter_data['sort'], $v['value']) === 0) {
                    $result['sort'] = $result['all_sorts'][$k];
                }
            }
        }

        if ($product_total) {
            $result['min_den'] = (int) $product_total['min_den'];
            $result['max_den'] = (int) $product_total['max_den'];
            $result['min_price'] = (int) $product_total['min_price'];
            $result['max_price'] = (int) $product_total['max_price'];
        }

        if (isset($filter_data['filter_category_id'])) {
            $result['category_id'] = $filter_data['filter_category_id'];
        }
        if (isset($filter_data['filter_discount_id'])) {
            $result['discount_id'] = $filter_data['filter_discount_id'];

            if ($result['discount_id'] && !$result['category_id']) {
                $result['category_id'] = '';

                $this->load->model('extension/total/pro_discount');
                $result['discount_data'] = $this->model_extension_total_pro_discount->getDiscountData(
                    $result['discount_id']);
            }
        }
        if (isset($filter_data['page'])) {
            $result['page'] = (int)$filter_data['page'];
        }
        if (isset($filter_data['path']) && !empty($filter_data['path'])) {
            $result['path'] = (string)$filter_data['path'];
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

        if (isset($filter_data['material'])) {
            $material = $filter_data['material'];
        }
        if (isset($filter_data['color'])) {
            $color = $filter_data['color'];
        }
        if (isset($filter_data['size'])) {
            $size = $filter_data['size'];
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
        $result['all_materials'] = array();
        $materials = $this->model_catalog_product->getMaterialsForFilter($filter_data);

        $materials_check = array();
        $materials = array_filter($materials, function($v) {
            if ($v['value']) { return true; }
        });

        foreach ($materials as $m) {
            if (!in_array(trim($m['value']), $materials_check)) {
                $materials_check[] = trim($m['value']);
                $result['all_materials'][] = $m;

                if (isset($material) && trim($material) == trim($m['value'])) {
                    $result['material'] = $m;
                }
            }
        }

        // ALL COLORS
        $result['all_colors'] = array();
        $colors = $this->model_catalog_product->getColorsForFilter($filter_data);

        $colors_check = array();
        $colors = array_filter($colors, function($v) {
            if ($v['value']) { return true; }
        });

        foreach ($colors as $c) {
            preg_match('/\((?<name>.+)\)/', $c['label'], $matches);
            if (isset($matches['name'])) {
                $matches['name'] = trim($matches['name']);

                if (!in_array($matches['name'], $colors_check)) {
                    $colors_check[] = $matches['name'];

                    $result['all_colors'][] = array(
                        'label' => $matches['name'],
                        'value' => $matches['name'],
                    );

                    if (isset($color) && trim($color) == $matches['name']) {
                        $result['color'] = array(
                            'label' => $matches['name'],
                            'value' => $matches['name'],
                        );
                    }
                }
            }
        }

        // ALL SIZES
        $result['all_sizes'] = array();
        $sizes = $this->model_catalog_product->getSizesForFilter($filter_data);

        $sizes_check = array();
        $sizes = array_filter($sizes, function($v) {
            if ($v['value']) { return true; }
        });

        foreach ($sizes as $s) {
            if (!in_array($s['value'], $sizes_check)) {
                $sizes_check[] = $s['value'];
                $result['all_sizes'][] = $s;

                if (isset($size) && $size == $s['value']) {
                    $result['size'] = $s;
                }
            }
        }

        // SORT SIZES
        if (!empty($result['all_sizes'])) {
            usort($result['all_sizes'], function($a,$b) {
                return strcmp(strval($a['label']), strval($b['label']));
            });
        }

        // SORT COLORS
        if (!empty($result['all_colors'])) {
            usort($result['all_colors'], function($a,$b) {
                return strcmp(strval($a['label']), strval($b['label']));
            });
        }

        $time_end = microtime(true);
        $time_end = microtime(true);
        $this->timing[] = array(
            'FILTER VALUES' => round(($time_end - $time_start) * 1000),
        );

        return $result;
    }

    public function prepareInitialData($filter_data = array())
    {
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

        $sort = 'pd.name';
        $order = 'ASC';
        $page = 1;
        $limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
        $category_id = 0;
        $discount_id = 0;
        $search = null;
        $path = '';

        /* FROM GET PARAMS START */
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        }
        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        }
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        }
        if (isset($this->request->get['limit'])) {
            $limit = (int)$this->request->get['limit'];
        }
        if (isset($this->request->get['search'])) {
            $search = $this->request->get['search'];
        }
        if (isset($this->request->get['path'])) {
            $parts = explode('_', (string)$this->request->get['path']);
            $category_id = (int)array_pop($parts);
        }
        if (isset($this->request->get['discount_id'])) {
            $discount_id = (int)$this->request->get['discount_id'];
        }
        if (isset($this->request->get['path']) && !empty($this->request->get['path'])) {
            $path = $this->request->get['path'];
        }
        if (isset($this->request->get['act'])) {
            $act = (bool) $this->request->get['act'];
        }
        if (isset($this->request->get['neww'])) {
            $neww = (bool) $this->request->get['neww'];
        }
        if (isset($this->request->get['hit'])) {
            $hit = (bool) $this->request->get['hit'];
        }
        if (isset($this->request->get['min_den'])) {
            $min_den = (float)$this->request->get['min_den'];
        }
        if (isset($this->request->get['max_den']) && (float)$this->request->get['max_den'] > 0) {
            $max_den = (float)$this->request->get['max_den'];
        }
        if (isset($this->request->get['min_price'])) {
            $min_price = (float)$this->request->get['min_price'];
        }
        if (isset($this->request->get['max_price']) && (float)$this->request->get['max_price'] > 0) {
            $max_price = (float)$this->request->get['max_price'];
        }
        if (isset($this->request->get['material']) && !empty($this->request->get['material'])) {
            $material = $this->request->get['material'];
        }
        if (isset($this->request->get['color']) && !empty($this->request->get['color'])) {
            $color = $this->request->get['color'];
        }
        if (isset($this->request->get['size']) && !empty($this->request->get['size'])) {
            $size = (int) $this->request->get['size'];
        }
        if (isset($this->request->get['manufacturers'])) {
            $manufacturers = array_filter(explode(",", $this->request->get['manufacturers']));
        }
        /* FROM GET PARAMS END */

        /* FROM FILTER START */
        if (isset($filter_data['page'])) {
            $page = (int)$filter_data['page'];
        }
        if (isset($filter_data['category_id'])) {
            $category_id = (int)$filter_data['category_id'];
        }
        if (isset($filter_data['discount_id'])) {
            $discount_id = (int)$filter_data['discount_id'];
        }
        if (isset($filter_data['min_den'])) {
            $min_den = (float)$filter_data['min_den'];
        }
        if (isset($filter_data['max_den']) && (float)$filter_data['max_den'] > 0) {
            $max_den = (float)$filter_data['max_den'];
        }
        if (isset($filter_data['min_price'])) {
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
        if (isset($filter_data['sort']) && is_array($filter_data['sort'])
        && isset($filter_data['sort']['value']) && !empty($filter_data['sort']['value'])) {
            $sort = (string)$filter_data['sort']['value'];
        }
        /* FROM FILTER END */

        if ($discount_id && !$category_id) {
            $category_id = '';
        }

        $prepared = array(
            'filter_category_id'  => $category_id,
            'filter_discount_id'  => $discount_id,
            'filter_sub_category' => true,
            'filter_description'  => true,
            'filter_h1'           => true,
            'filter_attributes'   => true,
            'filter_sd'           => true,
            'sort'                => $sort,
            'order'               => $order,
            'start'               => ($page - 1) * $limit,
            'page'                => $page,
            'limit'               => $limit,
        );

        if ($discount_id && !$category_id) {
            $this->load->model('extension/total/pro_discount');
            $prepared['discount_data'] = $this->model_extension_total_pro_discount->getDiscountData(
                $discount_id);
        }

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

        $color_id = $this->getColorId();
        if ($color_id !== null) {
            $prepared['color_id'] = $color_id;
        }

        $size_id = $this->getSizeId();
        if ($size_id !== null) {
            $prepared['size_id'] = $size_id;
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

    private function getColorId()
    {
        $q = $this->db->query("SELECT o.option_id FROM " . DB_PREFIX . "option o
            LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id)
            WHERE LCASE(od.name) = 'цвет'");

        if (isset($q->row['option_id'])) {
            return (int)$q->row['option_id'];
        }

        return null;
    }

    private function getSizeId()
    {
        $q = $this->db->query("SELECT o.option_id FROM " . DB_PREFIX . "option o
            LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id)
            WHERE LCASE(od.name) = 'размер'");

        if (isset($q->row['option_id'])) {
            return (int)$q->row['option_id'];
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

        $result = array(
            'den' => $this->getSliderOptions($product_total['min_den'], $product_total['max_den']),
            'price' => $this->getSliderOptions($product_total['min_price'], $product_total['max_price']),
        );

        if ($result['den']['max'] == 0) {
            $result['den']['max'] = 1000;
        }
        if ($result['price']['max'] == 0) {
            $result['price']['max'] = 10000;
        }

        return $result;
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

    public function getDefaultFilterQueryParams()
    {
        return array(
            'hit',
            'act',
            'neww',
            'min_den',
            'max_den',
            'min_price',
            'max_price',
            'color',
            'material',
            'size',
            'search',
            'manufacturers',
        );
    }

}
