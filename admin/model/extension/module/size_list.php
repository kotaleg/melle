<?php
/*
 *  location: admin/model
 */
class ModelExtensionModuleSizeList extends Model
{
    private $codename = 'size_list';
    private $route = 'extension/module/size_list';

    const IMAGE_TABLE = 'sl_images';
    const PRODUCT_TABLE = 'sl_product';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/setting');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/db');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);
    }

    public function createTables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . self::IMAGE_TABLE ."` (
            `image_id` int(11) NOT NULL AUTO_INCREMENT,

            `name` varchar(255) NOT NULL,
            `image` varchar(255) NOT NULL,

            PRIMARY KEY (`image_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . self::PRODUCT_TABLE ."` (
            `image_id` int(11) NOT NULL,
            `product_id` int(11) NOT NULL,

            PRIMARY KEY (`image_id`, `product_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");
    }

    public function dropTables()
    {
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . self::IMAGE_TABLE ."`");
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . self::PRODUCT_TABLE ."`");
    }

    public function getScriptFiles()
    {
        if (isset($this->setting['debug']) && $this->setting['debug']) {
            $rand = '?'.rand(777, 999);
        } else { $rand = ''; }

        $scripts = array();
        $scripts[] = "view/javascript/{$this->codename}/dist/{$this->codename}.js{$rand}";

        return $scripts;
    }

    public function prepareForTree($data)
    {
        $result = array();

        if (is_array($data)) {
            foreach ($data as $item) {

                $result[] = array(
                    'id'    => $item['id'],
                    'label' => $item['name'],
                );
            }
        }

        return $result;
    }

    public function getItems()
    {
        $images = array();

        $q = $this->db->query("SELECT `image_id`
            FROM `". DB_PREFIX . self::IMAGE_TABLE ."`");

        foreach ($q->rows as $item) {
            $d = $this->getItem($item['image_id']);
            if ($d) { $images[] = $d; }
        }

        return $images;
    }

    public function getItem($image_id)
    {
        $image = array(
            'image_id' => '',
            'name' => '',
            'image' => '',
            'thumb' => '',
            'count' => 0,
        );

        if ($image_id) {
            $q = $this->db->query("SELECT *
                FROM `". DB_PREFIX . self::IMAGE_TABLE ."`
                WHERE `image_id` = '" . (int)$image_id . "'");

            if (isset($q->row['image_id'])) {
                $image['image_id'] = (int)$q->row['image_id'];
                $image['image'] = $q->row['image'];
                $image['name'] = $q->row['name'];
            }
        }

        $this->load->model('tool/image');

        if (isset($image['image']) && is_file(DIR_IMAGE . $image['image'])) {
            $image['thumb'] = $this->model_tool_image->resize($image['image'], 100, 100);
        } else {
            $image['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        }

        return $image;
    }

    public function saveItem($data)
    {
        $json['saved'] = false;

        if (empty($data['image'])) {
            $data['image'] = $data['image_original'];
        }

        if (empty($data['image'])) {
            $json['error'][] = 'Неверное изображение';
        }

        if ((utf8_strlen($data['name']) < 1) || (utf8_strlen($data['name']) > 32)) {
            $json['error'][] = 'Какое то хреновое имя';
        }

        if (!isset($json['error'])) {

            if (!$data['image_id']) {
                $this->db->query("INSERT INTO `". DB_PREFIX . self::IMAGE_TABLE ."`
                    (`name`, `image`)
                    VALUES (
                        '". $this->db->escape($data['name']) ."',
                        '". $this->db->escape($data['image']) ."'
                    )");

                $data['image_id'] = $this->db->getLastId();

                $json['success'][] = 'Элемент создан';
            } else {
                $this->db->query("UPDATE `". DB_PREFIX . self::IMAGE_TABLE ."`
                    SET `name` = '". $this->db->escape($data['name']) ."',
                        `image` = '". $this->db->escape($data['image']) ."'
                    WHERE `image_id` = '" . (int)$data['image_id'] . "'");

                $json['success'][] = 'Данные элемента обновлены';
            }

            $json['saved'] = true;
        }

        return $json;
    }

    public function removeItem($image_id)
    {
        $this->db->query("DELETE FROM `". DB_PREFIX . self::IMAGE_TABLE ."`
            WHERE `image_id` = '" . (int)$image_id . "'");
        $this->db->query("DELETE FROM `". DB_PREFIX . self::PRODUCT_TABLE ."`
            WHERE `image_id` = '" . (int)$image_id . "'");
    }

    public function saveImageForProduct($product_id, $image_id)
    {
        $this->db->query("DELETE FROM `". DB_PREFIX . self::PRODUCT_TABLE ."`
            WHERE `product_id` = '" . (int)$product_id . "'");

        if ($image_id) {
            $this->db->query("INSERT INTO `". DB_PREFIX . self::PRODUCT_TABLE ."`
                SET `image_id` = '" . (int)$image_id . "',
                    `product_id` = '" . (int)$product_id . "'");
        }
    }

    public function getImageForProduct($product_id)
    {
        $image = '';

        if ($product_id) {
            $q = $this->db->query("SELECT *
                FROM `". DB_PREFIX . self::PRODUCT_TABLE ."`
                WHERE `product_id` = '" . (int)$product_id . "'
                LIMIT 1");

            if ($q->row) {
                $image = $q->row['image_id'];
            }
        }

        return $image;
    }

    public function getAllSizeList($query = null, $product_id = null)
    {
        $sql = "SELECT i.image_id AS id, i.name
            FROM `". DB_PREFIX . self::IMAGE_TABLE ."` i";

        $glue = 'WHERE';

        if ($product_id !== null) {
            $sql .= " LEFT JOIN `". DB_PREFIX . self::PRODUCT_TABLE ."` dd ";
            $sql .= " ON(dd.image_id = m.image_id) ";
            $sql .= " WHERE dd.product_id = '" . (int)$product_id . "' ";
            $glue = 'AND';
        }

        if ($query !== null) {
            $sql .= " {$glue} i.name LIKE '%" . $this->db->escape($query) . "%' ";
        }

        $sql .= " LIMIT 20";

        return $this->db->query($sql)->rows;
    }

}