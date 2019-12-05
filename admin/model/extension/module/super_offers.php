<?php
/*
 *  location: admin/model
 */
class ModelExtensionModuleSuperOffers extends Model
{
    const OPTION_COMMBINATION = 'so_option_combination';
    const OPTION_CONNECTION = 'so_option_connection';
    const OPTION_SETTING = 'so_column_setting';

    const NULL_VALUE = '--';

    private $codename = 'super_offers';
    private $route = 'extension/module/super_offers';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/setting');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/db');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);

        if (!$this->super_offers) {
            $this->super_offers = new \super_offers($registry); }
    }

    public function createTables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . $this->db->escape(self::OPTION_COMMBINATION) ."` (
            `combination_id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL,

            `quantity` int(8) NOT NULL,
            `subtract` tinyint(1) NOT NULL,
            `price` decimal(15,4) NOT NULL,
            `price_prefix` varchar(1) NOT NULL,
            `points` int(8) NOT NULL,
            `points_prefix` varchar(1) NOT NULL,
            `weight` decimal(15,8) NOT NULL,
            `weight_prefix` varchar(1) NOT NULL,

            `model` varchar(64) NOT NULL,
            `product_code` varchar(64) NOT NULL,
            `special_price` decimal(15,4) NOT NULL,
            `special_price_start` int(11) NOT NULL,
            `special_price_end` int(11) NOT NULL,

            `image` varchar(255) NOT NULL,
            `barcode` varchar(64) NOT NULL,

            `import_id` varchar(255) NOT NULL,

            PRIMARY KEY (`combination_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . $this->db->escape(self::OPTION_CONNECTION) ."` (
            `connection_id` int(11) NOT NULL AUTO_INCREMENT,
            `option_a` int(11) NOT NULL,
            `option_value_a` int(11) NOT NULL,
            `product_id` int(11) NOT NULL,
            `combination_id` int(11) NOT NULL,
            PRIMARY KEY (`connection_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . $this->db->escape(self::OPTION_SETTING) ."` (
            `setting_id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL,
            `setting_data` TEXT NOT NULL,
            PRIMARY KEY (`setting_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");
    }

    public function dropTables()
    {
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . $this->db->escape(self::OPTION_COMMBINATION) ."`");
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . $this->db->escape(self::OPTION_CONNECTION) ."`");
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . $this->db->escape(self::OPTION_SETTING) ."`");
    }

    public function getScriptFiles($admin = false)
    {
        if (isset($this->setting['debug']) && $this->setting['debug']) {
            $rand = '?'.rand(777, 999);
        } else { $rand = ''; }

        $admin_postfix = ($admin === true) ? '_admin' : '';

        $scripts = array();
        $scripts[] = "view/javascript/{$this->codename}{$admin_postfix}/dist/{$this->codename}{$admin_postfix}.js{$rand}";

        return $scripts;
    }

    public function getOptionsAndValues($product_id)
    {
        $this->load->model('catalog/product');
        $this->load->model('catalog/option');
        $product_options = $this->model_catalog_product->getProductOptions($product_id);

        $data['product_options'] = false;

        $po = 0;
        foreach ($product_options as $product_option) {

            if (!in_array($product_option['type'], $this->super_offers->getSupportedOptionTypes())) {
                continue;
            }

            $product_option_value_data = array();

            if (isset($product_option['product_option_value'])) {
                $pov = 0;
                foreach ($product_option['product_option_value'] as $product_option_value) {
                    $product_option_value_data[] = array(
                        'product_option_value_id' => $product_option_value['product_option_value_id'],
                        'option_value_id'         => $product_option_value['option_value_id'],
                        'quantity'                => $product_option_value['quantity'],
                        'subtract'                => $product_option_value['subtract'],
                        'price'                   => $product_option_value['price'],
                        'price_prefix'            => $product_option_value['price_prefix'],
                        'points'                  => $product_option_value['points'],
                        'points_prefix'           => $product_option_value['points_prefix'],
                        'weight'                  => $product_option_value['weight'],
                        'weight_prefix'           => $product_option_value['weight_prefix']
                    );
                    $pov++;
                }
            }

            $data['product_options'][] = array(
                'product_option_id'    => $product_option['product_option_id'],
                'product_option_value' => $product_option_value_data,
                'option_id'            => $product_option['option_id'],
                'name'                 => $product_option['name'],
                'type'                 => $product_option['type'],
                'value'                => isset($product_option['value']) ? $product_option['value'] : '',
                'required'             => $product_option['required']
            );
            $po++;
        }

        $data['option_values'] = false;

        if (is_array($data['product_options'])) {

            foreach ($data['product_options'] as $product_option) {
                if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
                    if (!isset($data['option_values'][$product_option['option_id']])) {

                        $values = $this->model_catalog_option->getOptionValues($product_option['option_id']);
                        // $v = 0;
                        // foreach ($values as $k => $val) {
                        //     $values['v_'.$v] = $val;
                        //     unset($values[$k]);
                        //     $v++;
                        // }

                        $data['option_values'][$product_option['option_id']] = $values;
                    }
                }
            }
        }

        return array(
            'product_options'   => $data['product_options'],
            'option_values'     => $data['option_values'],
        );
    }

    public function activeColumnsFiller($state)
    {
        $active_columns = false;

        if (isset($state['options']) && is_array($state['options'])) {

            $i = 0;
            foreach ($state['options'] as $k => $option) {
                $active_columns[] = array(
                    'name'      => $option['name'],
                    'option_id' => $k,
                    'active'    => true
                );
                $i++;
            }
        }

        if ($active_columns && $i > 0) {
            foreach ($this->super_offers->getDefaultActiveColumns() as $column) {
                if (isset($state["text_{$column['code']}"])) {
                    $column['name'] = $state["text_{$column['code']}"];
                }
                $i++;
                $active_columns[] = $column;
            }
        }

        return $active_columns;
    }

    public function clearForProduct($product_id)
    {
        return $this->super_offers->clearForProduct($product_id);
    }

    public function getDefaultActiveColumns()
    {
        return $this->super_offers->getDefaultActiveColumns();
    }

    /* TODO: save option order in save process */
    public function saveCombinations($data)
    {
        if (!isset($data[0]) || !isset($data[1]['so_combination'])) {
            return;
        }

        return $this->super_offers->saveCombinations($data[0], $data[1]['so_combination']);
    }

    public function getCombinations($state, $get_data = false)
    {
        $combinations = array();

        if (!$state['options'] || !$state['option_values']) {
            return $combinations;
        }

        $comb_q = $this->db->query("SELECT * FROM `". DB_PREFIX . $this->db->escape(self::OPTION_COMMBINATION) ."`
            WHERE `product_id` = '". (int)$state['product_id'] ."'");

        $c = 0;
        foreach ($comb_q->rows as $combination) {

            if (!$get_data) {
                $co = $this->super_offers->_getConnectionsForCombination((int)$state['product_id'], (int)$combination['combination_id']);

                foreach ($co as $connection) {

                    foreach ($state['options'] as $ok => $ov) {
                        if (isset($ov['product_option_value']) && is_array($ov['product_option_value'])) {
                            foreach ($ov['product_option_value'] as $povk => $povv) {

                                if (($connection['option_a'] == $ov['option_id'])
                                && ($connection['option_value_a'] == $povv['option_value_id'])) {

                                    $combinations[$c][$ok] = $povk;
                                }
                            }
                        }
                    }
                }
            } else {
                $combinations[$c] = $combination;
            }

            $c++;
        }

        if (!$get_data) {
            // CLEAR COMBINATIONS THAT ARE NOT FOR CURRENT OPTIONS COUNT
            if ($combinations && is_array($state['options'])) {
                $options_count = count($state['options']);
                foreach ($combinations as $k => $v) {
                    if (is_array($v) && (count($v) != $options_count)) {
                        unset($combinations[$k]);
                    }
                }
            }
        }

        return $combinations;
    }

    public function getCombinationsData($state)
    {
        $combinations_data = array();
        if (!is_array($state['combinations'])) { return $combinations_data; }
        $combinations_extended = $this->getCombinations($state, true);

        foreach ($state['combinations'] as $combination_key => $combination_value) {

            $q = '';
            $s = false;
            $p = '';
            $m = '';
            $img = '';

            if (array_key_exists($combination_key, $combinations_extended)) {

                if ($combinations_extended[$combination_key]['quantity'] != self::NULL_VALUE) {
                    $q = $combinations_extended[$combination_key]['quantity'];
                }

                $s = ($combinations_extended[$combination_key]['subtract']) ? true : false;
                $p = $combinations_extended[$combination_key]['price'];
                $m = $combinations_extended[$combination_key]['model'];
                $img = $combinations_extended[$combination_key]['image'];
                $barcode = $combinations_extended[$combination_key]['barcode'];
            }

            $combinations_data[$combination_key] = array(
                'quantity'  => $q,
                'subtract'  => $s,
                'price'     => $p,
                'model'     => $m,
                'image'     => $img,
                'barcode'   => $barcode,
                'hided_by_filter' => false,
            );
        }

        return $combinations_data;
    }

    public function prepareImagesForProduct($productId)
    {
        $images = array();

        $dpi = $this->getDefaultProductImage($productId);
        if ($dpi && $this->isImageExist($dpi)) {
            $images[] = array(
                'imagePath' => $dpi,
                'hash' => md5($dpi),
                'imagePreview' => $this->getImagePreview($dpi),
                'imagePopup' => $this->getImagePopup($dpi),
            );
        }

        foreach ($this->getProductImages($productId) as $img) {
            if (isset($img['image']) && $img['image']
            && $this->isImageExist($img['image'])) {
                $images[] = array(
                    'imagePath' => $img['image'],
                    'hash' => md5($img['image']),
                    'imagePreview' => $this->getImagePreview($img['image']),
                    'imagePopup' => $this->getImagePopup($img['image']),
                );
            }
        }

        return $images;
    }

    private function isImageExist($imagePath)
    {
        if (is_file(DIR_IMAGE . $imagePath)) {
            return true;
        }
        return false;
    }

    private function getImagePreview($imagePath)
    {
        $this->load->model('tool/image');
        return $this->model_tool_image->resize($imagePath, 30, 30, true);
    }

    private function getImagePopup($imagePath)
    {
        $this->load->model('tool/image');
        return $this->model_tool_image->resize($imagePath, 150, 150, true);
    }

    private function getDefaultProductImage($productId)
    {
        $q = $this->db->query("SELECT `image` FROM `". DB_PREFIX . "product`
            WHERE `product_id` = '". (int)$productId ."'");

        if (isset($q->row['image']) && $q->row['image']) {
            return $q->row['image'];
        }
    }

    private function getProductImages($productId)
    {
        $q = $this->db->query("SELECT * FROM `". DB_PREFIX . "product_image`
            WHERE `product_id` = '". (int)$productId ."'
            ORDER BY `sort_order` ASC");

        return $q->rows;
    }
}