<?php
class ModelApiImport1CProduct extends Model
{
    private $codename = 'product';
    private $route = 'api/import_1c/product';

    const PRODUCT_TABLE = 'product';

    const CATEGORY = 'Категория';
    const COLLECTION = 'Коллекция';
    const MATERIAL = 'Материал';
    const LENGTH = 'Длина';
    const WIDTH = 'Ширина';
    const HEIGTH = 'Высота';
    const TITLE = 'Заголовок';
    const TITLE_H1 = 'ЗаголовокH1';
    const DESCRIPTION = 'Описание';
    const KEYWORDS = 'КлючевыеСлова';

    const A_GROUP = 'Группа';
    const A_SOSTAV = 'Состав';
    const A_DEN = 'Ден';

    const SPRAVOSHNIK = 'Справочник';

    function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('api/import_1c/helper');
        $this->load->model('api/import_1c/group');
        $this->load->model('api/import_1c/progress');
        $this->load->model('extension/module/super_offers');
    }

    public function action($parsed, $languages, $exchange_path)
    {
        if (isset($parsed->catalog->products)) {

            $already_moved_images = 0;

            foreach ($parsed->catalog->products as $product) {

                $json = array();

                $d_ = array(
                    'import_id' => $product->id,
                    'model' => (empty($product->artikul)) ? '--' : $product->artikul,
                    'sku' => $product->artikul,
                    'upc' => '',
                    'ean' => '',
                    'jan' => '',
                    'isbn' => '',
                    'mpn' => '',
                    'image' => '',
                    'location' => '',
                    'quantity' => 0,
                    'minimum' => 1,
                    'subtract' => 1,
                    'stock_status_id' => 0,
                    'date_available' => '',
                    'shipping' => 1,
                    'price' => 0,
                    'points' => 0,
                    'weight' => 0,
                    'weight_class_id' => $this->config->get('config_weight_class_id'),
                    'length_class_id' => $this->config->get('config_length_class_id'),
                    'status' => 0,
                    'tax_class_id' => 0,
                    'sort_order' => 0,

                    'import_id' => $product->id,

                    'length' => '',
                    'height' => '',
                    'width' => '',
                    'manufacturer_id' => '',
                    'product_image' => array(),
                );

                // IMAGE
                if ($product->pictures) {
                    foreach ($product->pictures as $k => $pic) {
                        $picture_ = $this->imageHandler($exchange_path, $pic);

                        if ($picture_['path']) {
                            if ($k == 0) {
                                $d_['image'] = $picture_['path'];
                            } else {
                                $d_['product_image'][] = array(
                                    'image' => $picture_['path'],
                                    'sort_order' => $k,
                                );
                            }
                        }

                        if ($picture_['moved']) {
                            $already_moved_images++;
                        }
                    }
                }

                // SOSTAV
                $sostav = $this->prepareSostav($product->compositions);
                $attr = $this->model_api_import_1c_group->prepareProductAttribute(
                    self::A_SOSTAV, $sostav, $languages);
                if ($attr) {
                    $d_['product_attribute'][] = $attr;
                }
                unset($sostav, $group, $attr);

                // DEN
                if (isset($product->group) && $product->group) {
                    $attr = $this->model_api_import_1c_group->prepareProductAttribute(
                        self::A_DEN, $product->den, $languages);
                    if ($attr) {
                        $d_['product_attribute'][] = $attr;
                    }
                    unset($group, $attr);
                }

                $title = '';
                $title_h1 = '';
                $description = '';
                $keywords = '';

                if (is_array($product->options)) {
                    foreach ($product->options as $option) {
                        $option_data = $this->getOptionData($parsed, $option);

                        switch ($option_data['name']) {
                            case self::LENGTH:
                                $d_['length'] = $option_data['value'];
                                break;
                            case self::HEIGTH:
                                $d_['height'] = $option_data['value'];
                                break;
                            case self::WIDTH:
                                $d_['width'] = $option_data['value'];
                                break;
                            case self::TITLE:
                                $title = $option_data['value'];
                                break;
                            case self::TITLE_H1:
                                $title_h1 = $option_data['value'];
                                break;
                            case self::DESCRIPTION:
                                $description = $option_data['value'];
                                break;
                            case self::KEYWORDS:
                                $keywords = $option_data['value'];
                                break;

                            case self::CATEGORY:
                                $category_id = $this->getCategoryByImportId($option_data['import_id']);
                                if ($category_id) {
                                    $d_['product_category'] = array(
                                        0 => $category_id,
                                    );
                                }
                                unset($category_id);
                                break;

                            case self::COLLECTION:
                                $attr = $this->model_api_import_1c_group->prepareProductAttribute(
                                    self::COLLECTION, $option_data['value'], $languages);
                                if ($attr) {
                                    $d_['product_attribute'][] = $attr;
                                }
                                unset($attr);
                                break;

                            case self::MATERIAL:
                                $attr = $this->model_api_import_1c_group->prepareProductAttribute(
                                    self::MATERIAL, $option_data['value'], $languages);
                                if ($attr) {
                                    $d_['product_attribute'][] = $attr;
                                }
                                unset($attr);
                                break;
                        }
                    }
                }

                // DESCRIPTION
                if (empty($title)) { $title = $product->name; }

                foreach ($languages as $l) {
                    $d_['product_description'][$l] = array(
                        'name' => $product->name,
                        'description' => $product->description,
                        'tag' => '',
                        'meta_title' => $title,
                        'meta_description' => $description,
                        'meta_keyword' => $keywords,
                        'small_description' => $description,
                        'h1' => $title_h1,
                    );
                }

                // STORE
                $d_['product_store'] = array(
                    0 => $this->config->get('config_store_id'),
                );

                // GROUP
                if (isset($product->group->id)) {
                    $category_id = $this->getCategoryByImportId($product->group->id);
                    if ($category_id) {
                        if (isset($d_['product_category'])) {
                            if (!is_array($d_['product_category'])) {
                                $d_['product_category'] = array();
                            }
                        }
                        $d_['product_category'][] = $category_id;
                    }
                    unset($category_id);
                }

                // PRODUCER
                if (isset($product->producer->id)) {
                    $manufacturer_id = $this->getManufacturerByImportId($product->producer->id);
                    if ($manufacturer_id) {
                        $d_['manufacturer_id'] = $manufacturer_id;
                    }
                    unset($manufacturer_id);
                }

                if (!$this->model_api_import_1c_helper->isImportRecordExist(
                    self::PRODUCT_TABLE, $product->id)) {
                    $this->addProduct($d_);
                } else {
                    $product_id = $this->getProductByImportId($product->id);
                    $this->editProduct($product_id, $d_);
                }

                // SAVE TO LOG
                $this->model_api_import_1c_progress->parseJson($json);
            }

            if ($already_moved_images) {
                $json['message'] = array();
                $json['message'][] = "Already moved images = {$already_moved_images}";

                // SAVE TO LOG
                $this->model_api_import_1c_progress->parseJson($json);
            }
        }
    }

    private function imageHandler($exchange_path, $picture)
    {
        $result = array(
            'path' => false,
            'moved' => false,
        );

        $img = $this->moveImage($exchange_path, $picture);
        if ($img) {
            $result['path'] = $img;
        } else {
            // CHECK IF IMAGE ALREADY EXIST
            if (is_readable($this->newImagePath($picture))) {
                $result['moved'] = true;
                $result['path'] = $this->newImagePath($picture, false);
            }
        }

        return $result;
    }

    private function newImagePath($picture, $full = true)
    {
        $pre = ($full) ? DIR_IMAGE : '';
        return $pre . 'catalog/' . $picture;
    }

    private function moveImage($exchange_path, $picture)
    {
        $current_path = "{$exchange_path}{$picture}";

        if (is_file($current_path) && is_readable($current_path)) {
            $new_path = $this->newImagePath($picture);

            if (!is_dir(dirname($new_path))) {
                $d = new \import_1c\import_1c_dir;
                $d::createDir(dirname($new_path));
                unset($d);
            }

            if (@rename($current_path, $new_path) === true) {
                return $this->newImagePath($picture, false);
            }
        }
    }

    private function getGroupData($parsed, $p_group)
    {
        $result = array(
            'import_id' => null,
            'name' => null,
        );

        if (isset($p_group->id)) {
            foreach ($parsed->classificator->groups as $group) {
                if (strcmp((string)$group->id, (string)$p_group->id) === 0) {
                    $result['import_id'] = $p_group->id;
                    $result['name'] = $group->name;
                }
            }
        }

        return $result;
    }

    private function getCategoryByImportId($import_id)
    {
        $query = $this->db->query("SELECT `category_id`
            FROM `". DB_PREFIX ."category`
            WHERE `import_id` = '".$this->db->escape($import_id)."'");
        if ($query->row) {
            return $query->row['category_id'];
        }
    }

    private function getManufacturerByImportId($import_id)
    {
        $query = $this->db->query("SELECT `manufacturer_id`
            FROM `". DB_PREFIX ."manufacturer`
            WHERE `import_id` = '".$this->db->escape($import_id)."'");
        if ($query->row) {
            return $query->row['manufacturer_id'];
        }
    }

    public function getProductByImportId($import_id)
    {
        $query = $this->db->query("SELECT `product_id`
            FROM `". DB_PREFIX ."product`
            WHERE `import_id` = '".$this->db->escape($import_id)."'");
        if ($query->row) {
            return $query->row['product_id'];
        }
    }

    private function prepareSostav($compositions)
    {
        $sostav = '';
        if (is_array($compositions)) {
            $column_names = array_keys($compositions);
            $last_column = array_pop($column_names);
            foreach ($compositions as $c => $v) {
                $coma = ($c === $last_column) ? '' : ',';
                $sostav .= "{$v->name} {$v->percent}%{$coma} ";
            }
        }
        return trim($sostav);
    }

    private function getOptionData($parsed, $p_option)
    {
        $result = array(
            'name' => null,
            'value' => null,
        );

        foreach ($parsed->classificator->options as $option) {
            if (strcmp((string)$option->id, (string)$p_option->id) === 0) {
                $result['name'] = trim($option->name);

                if (strcmp((string)trim($option->type), self::SPRAVOSHNIK) === 0) {
                    foreach ($option->variants as $variant) {
                        if (strcmp((string)$variant->id, (string)$p_option->value) === 0) {
                            $result['import_id'] = trim((string)$variant->id);
                            $result['value'] = trim((string)$variant->value);
                        }
                    }
                } else {
                    $result['value'] = trim($p_option->value);
                }
            }
        }

        return $result;
    }

    public function addProduct($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "product
            SET model = '" . $this->db->escape($data['model']) . "',
                `import_id` = '" . $this->db->escape($data['import_id']) . "',
                sku = '" . $this->db->escape($data['sku']) . "',
                upc = '" . $this->db->escape($data['upc']) . "',
                ean = '" . $this->db->escape($data['ean']) . "',
                jan = '" . $this->db->escape($data['jan']) . "',
                isbn = '" . $this->db->escape($data['isbn']) . "',
                mpn = '" . $this->db->escape($data['mpn']) . "',
                location = '" . $this->db->escape($data['location']) . "',
                quantity = '" . (int)$data['quantity'] . "',
                minimum = '" . (int)$data['minimum'] . "',
                subtract = '" . (int)$data['subtract'] . "',
                stock_status_id = '" . (int)$data['stock_status_id'] . "',
                date_available = '" . $this->db->escape($data['date_available']) . "',
                manufacturer_id = '" . (int)$data['manufacturer_id'] . "',
                shipping = '" . (int)$data['shipping'] . "',
                price = '" . (float)$data['price'] . "',
                points = '" . (int)$data['points'] . "',
                weight = '" . (float)$data['weight'] . "',
                weight_class_id = '" . (int)$data['weight_class_id'] . "',
                length = '" . (float)$data['length'] . "',
                width = '" . (float)$data['width'] . "',
                height = '" . (float)$data['height'] . "',
                length_class_id = '" . (int)$data['length_class_id'] . "',
                status = '" . (int)$data['status'] . "',
                tax_class_id = '" . (int)$data['tax_class_id'] . "',
                sort_order = '" . (int)$data['sort_order'] . "',
                date_added = NOW(),
                date_modified = NOW()");

        $product_id = $this->db->getLastId();

        if (isset($data['image'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product
                SET image = '" . $this->db->escape($data['image']) . "'
                WHERE product_id = '" . (int)$product_id . "'");
        }

        foreach ($data['product_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "product_description
                SET product_id = '" . (int)$product_id . "',
                    language_id = '" . (int)$language_id . "',
                    name = '" . $this->db->escape($value['name']) . "',
                    description = '" . $this->db->escape($value['description']) . "',
                    tag = '" . $this->db->escape($value['tag']) . "',
                    meta_title = '" . $this->db->escape($value['meta_title']) . "',
                    meta_description = '" . $this->db->escape($value['meta_description']) . "',
                    meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "',
                    small_description = '" . $this->db->escape($value['small_description']) . "',
                    h1 = '" . $this->db->escape($value['h1']) . "'");
        }

        if (isset($data['product_store'])) {
            foreach ($data['product_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store
                    SET product_id = '" . (int)$product_id . "',
                        store_id = '" . (int)$store_id . "'");
            }
        }

        if (isset($data['product_attribute'])) {
            foreach ($data['product_attribute'] as $product_attribute) {
                if ($product_attribute['attribute_id']) {
                    // Removes duplicates
                    $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute
                        WHERE product_id = '" . (int)$product_id . "'
                        AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

                    foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
                        $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute
                            WHERE product_id = '" . (int)$product_id . "'
                            AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'
                            AND language_id = '" . (int)$language_id . "'");

                        $this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute
                            SET product_id = '" . (int)$product_id . "',
                                attribute_id = '" . (int)$product_attribute['attribute_id'] . "',
                                language_id = '" . (int)$language_id . "',
                                text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
                    }
                }
            }
        }

        if (isset($data['product_option'])) {
            foreach ($data['product_option'] as $product_option) {
                if ($product_option['type'] == 'select' || $product_option['type'] == 'radio'
                || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {

                    if (isset($product_option['product_option_value'])) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "product_option
                            SET product_id = '" . (int)$product_id . "',
                                option_id = '" . (int)$product_option['option_id'] . "',
                                required = '" . (int)$product_option['required'] . "'");

                        $product_option_id = $this->db->getLastId();

                        foreach ($product_option['product_option_value'] as $product_option_value) {
                            $this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value
                                SET product_option_id = '" . (int)$product_option_id . "',
                                    product_id = '" . (int)$product_id . "',
                                    option_id = '" . (int)$product_option['option_id'] . "',
                                    option_value_id = '" . (int)$product_option_value['option_value_id'] . "',
                                    quantity = '" . (int)$product_option_value['quantity'] . "',
                                    subtract = '" . (int)$product_option_value['subtract'] . "',
                                    price = '" . (float)$product_option_value['price'] . "',
                                    price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "',
                                    points = '" . (int)$product_option_value['points'] . "',
                                    points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "',
                                    weight = '" . (float)$product_option_value['weight'] . "',
                                    weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
                        }
                    }
                } else {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_option
                        SET product_id = '" . (int)$product_id . "',
                            option_id = '" . (int)$product_option['option_id'] . "',
                            value = '" . $this->db->escape($product_option['value']) . "',
                            required = '" . (int)$product_option['required'] . "'");
                }
            }
        }

        if (isset($data['product_category'])) {
            foreach ($data['product_category'] as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category
                    SET product_id = '" . (int)$product_id . "',
                        category_id = '" . (int)$category_id . "'");
            }
        }

        if (isset($data['product_image'])) {
            foreach ($data['product_image'] as $product_image) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_image
                    SET product_id = '" . (int)$product_id . "',
                    image = '" . $this->db->escape($product_image['image']) . "',
                    sort_order = '" . (int)$product_image['sort_order'] . "'");
            }
        }

        $this->cache->delete('product');
        return $product_id;
    }

    public function editProduct($product_id, $data)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "product
            SET model = '" . $this->db->escape($data['model']) . "',
                `import_id` = '" . $this->db->escape($data['import_id']) . "',
                sku = '" . $this->db->escape($data['sku']) . "',
                upc = '" . $this->db->escape($data['upc']) . "',
                ean = '" . $this->db->escape($data['ean']) . "',
                jan = '" . $this->db->escape($data['jan']) . "',
                isbn = '" . $this->db->escape($data['isbn']) . "',
                mpn = '" . $this->db->escape($data['mpn']) . "',
                location = '" . $this->db->escape($data['location']) . "',
                quantity = '" . (int)$data['quantity'] . "',
                minimum = '" . (int)$data['minimum'] . "',
                subtract = '" . (int)$data['subtract'] . "',
                stock_status_id = '" . (int)$data['stock_status_id'] . "',
                date_available = '" . $this->db->escape($data['date_available']) . "',
                manufacturer_id = '" . (int)$data['manufacturer_id'] . "',
                shipping = '" . (int)$data['shipping'] . "',
                price = '" . (float)$data['price'] . "',
                points = '" . (int)$data['points'] . "',
                weight = '" . (float)$data['weight'] . "',
                weight_class_id = '" . (int)$data['weight_class_id'] . "',
                length = '" . (float)$data['length'] . "',
                width = '" . (float)$data['width'] . "',
                height = '" . (float)$data['height'] . "',
                length_class_id = '" . (int)$data['length_class_id'] . "',
                status = '" . (int)$data['status'] . "',
                tax_class_id = '" . (int)$data['tax_class_id'] . "',
                sort_order = '" . (int)$data['sort_order'] . "',
                date_modified = NOW()
            WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['image'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product
                SET image = '" . $this->db->escape($data['image']) . "'
                WHERE product_id = '" . (int)$product_id . "'");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_description
            WHERE product_id = '" . (int)$product_id . "'");

        foreach ($data['product_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "product_description
                SET product_id = '" . (int)$product_id . "',
                    language_id = '" . (int)$language_id . "',
                    name = '" . $this->db->escape($value['name']) . "',
                    description = '" . $this->db->escape($value['description']) . "',
                    tag = '" . $this->db->escape($value['tag']) . "',
                    meta_title = '" . $this->db->escape($value['meta_title']) . "',
                    meta_description = '" . $this->db->escape($value['meta_description']) . "',
                    meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "',
                    small_description = '" . $this->db->escape($value['small_description']) . "',
                    h1 = '" . $this->db->escape($value['h1']) . "'");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store
            WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['product_store'])) {
            foreach ($data['product_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store
                    SET product_id = '" . (int)$product_id . "',
                        store_id = '" . (int)$store_id . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute
            WHERE product_id = '" . (int)$product_id . "'");

        if (!empty($data['product_attribute'])) {
            foreach ($data['product_attribute'] as $product_attribute) {
                if ($product_attribute['attribute_id']) {
                    // Removes duplicates
                    $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute
                        WHERE product_id = '" . (int)$product_id . "'
                        AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

                    foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute
                            SET product_id = '" . (int)$product_id . "',
                                attribute_id = '" . (int)$product_attribute['attribute_id'] . "',
                                language_id = '" . (int)$language_id . "',
                                text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
                    }
                }
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_image
            WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['product_image'])) {
            foreach ($data['product_image'] as $product_image) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_image
                    SET product_id = '" . (int)$product_id . "',
                    image = '" . $this->db->escape($product_image['image']) . "',
                    sort_order = '" . (int)$product_image['sort_order'] . "'");
            }
        }

        $this->cache->delete('product');
    }

    public function updateProductStatus($product_id, $status)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "product
            SET status = '" . (int)$status . "'
            WHERE product_id = '" . (int)$product_id . "'");
    }

    public function deleteAllProducts()
    {
        $query = $this->db->query("SELECT `product_id` FROM `". DB_PREFIX ."product`");

        foreach ($query->rows as $product) {
            $this->deleteProduct($product['product_id']);
            $this->model_extension_module_super_offers->clearForProduct($product['product_id']);
        }
    }

    private function deleteProduct($product_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_recurring WHERE product_id = " . (int)$product_id);
        $this->db->query("DELETE FROM " . DB_PREFIX . "review WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE product_id = '" . (int)$product_id . "'");

        $this->cache->delete('product');
    }

    public function deleteProductOptions($product_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
    }

    public function isImageForProduct($path)
    {
        $query = $this->db->query("SELECT `product_id`
            FROM `". DB_PREFIX ."product`
            WHERE `image` = '".$this->db->escape($path)."'");
        if ($query->num_rows) {
            return $query->row['product_id'];
        }
    }
}