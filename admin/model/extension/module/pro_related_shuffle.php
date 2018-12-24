<?php
/*
 *  location: admin/model
 */
class ModelExtensionModuleProRelatedShuffle extends Model
{
    private $codename = 'pro_related_shuffle';
    private $route = 'extension/module/pro_related_shuffle';

    private $pro_cache;

    const PRODUCT_TABLE = 'pro_related_shuffle_temp';
    const RESHUFFLE_CACHE = 'reshuffle_cache';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/setting');

        if (!$this->pro_cache) { $this->pro_cache = new \pro_cache\pro_cache(DIR_CACHE.DIRECTORY_SEPARATOR.$this->codename); }
    }

    public function createTables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . $this->db->escape(self::PRODUCT_TABLE) ."` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `product_id` int(11) NOT NULL,
            `is_shuffled` tinyint(3) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `no_duplicate` (`product_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");
    }

    public function dropTables()
    {
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . $this->db->escape(self::PRODUCT_TABLE) ."`");
    }

    public function getScriptFiles()
    {
        $scripts = array();
        $scripts[] = "view/javascript/{$this->codename}/dist/js/{$this->codename}-vendors.js";
        $scripts[] = "view/javascript/{$this->codename}/dist/js/{$this->codename}-main.js";

        return $scripts;
    }

    public function parseJson($json)
    {
        $parsed = @json_decode($json, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return array('error' => 'Parse error.');
        }

        return $parsed === null ? array() : $parsed;
    }

    public function cancelShuffle()
    {
        $this->clearProductsTable();
    }

    public function shuffleAllProducts()
    {
        $setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);

        $key = md5(json_encode(array(__CLASS__, self::RESHUFFLE_CACHE)));
        $object = $this->pro_cache->getItem($key);

        $continue = false;
        $data = array(
            'products_count'    => 0,
            'processed_count'   => 0,
            'last_product'      => null,
        );

        if ($object->isHit()) { $data = $object->get(); }
        $object->lock();

        if ($data['last_product'] === null) {
            $this->clearProductsTable();
            $this->prepareProductsTable();
        }

        foreach ($this->getUnshaffledProducts($setting['proceed_count']) as $product_id) {
            $data['last_product'] = $product_id;

            // CLEAR RELATED
            $this->clearRelatedForProduct($product_id);

            // GET ALL POSSIBLE RELATED
            $this->getAppPossibleRelated($product_id, $setting);

            $this->updateShuffledStatus($product_id, true);
        }

        $data['products_count'] = $this->getProductsCount();
        $data['processed_count'] = $this->getProcessedProductsCount();

        $object->expiresAfter(21600);
        $object->set($data);
        $this->pro_cache->save($object);


        // INVALIDATION
        if ($data['products_count'] == 0
        || $data['products_count'] == $data['processed_count']) {
            $this->clearProductsTable();
            $this->pro_cache->deleteItem($key);
        } else {
            $continue = true;
        }

        return $continue;
    }

    public function shuffleRelatedForOneProduct($product_id)
    {
        $setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);

        // GET ALL POSSIBLE RELATED
        $this->getAppPossibleRelated($product_id, $setting);
    }

    private function getAppPossibleRelated($product_id, $setting)
    {
        $possible = $this->getProductsFromProductCategories($product_id, $setting['most_close_only']);
        $possible_count = count($possible);
        $count = ($possible_count < $setting['product_number']) ? $possible_count : $setting['product_number'];

        if ($count > 0) {
            $new_related = array_rand(array_flip($possible), $count);

            if (is_array($new_related)) {
                foreach ($new_related as $related_pid) {
                    if ($related_pid != $product_id) {
                        $this->addRelatedForProduct($product_id, $related_pid);
                    }
                }
            }
        }
    }

    private function clearProductsTable()
    {
        $this->db->query("TRUNCATE TABLE `". DB_PREFIX . $this->db->escape(self::PRODUCT_TABLE) ."`");
    }

    private function prepareProductsTable()
    {
        $this->db->query("INSERT INTO `". DB_PREFIX . $this->db->escape(self::PRODUCT_TABLE) ."`
            (`product_id`, `is_shuffled`)
            SELECT `product_id`, ". (int)false ." FROM `". DB_PREFIX ."product`");
    }

    private function getUnshaffledProducts($limit)
    {
        $products = array();
        $query = $this->db->query("SELECT `product_id` FROM `". DB_PREFIX . $this->db->escape(self::PRODUCT_TABLE) . "`
            WHERE `is_shuffled` = '" . (int)false . "'
            LIMIT ". (int)$limit);

        foreach ($query->rows as $product) {
            $products[] = $product['product_id'];
        }

        return $products;
    }

    private function getProductsCount()
    {
        $query = $this->db->query("SELECT COUNT(DISTINCT id) AS total
            FROM `". DB_PREFIX . $this->db->escape(self::PRODUCT_TABLE) . "`");

        return $query->row['total'];
    }

    private function getProcessedProductsCount()
    {
        $query = $this->db->query("SELECT COUNT(DISTINCT id) AS total
            FROM `". DB_PREFIX . $this->db->escape(self::PRODUCT_TABLE) . "`
            WHERE `is_shuffled` = '". (int)true ."'");

        return $query->row['total'];
    }

    private function updateShuffledStatus($product_id, $status)
    {
        $this->db->query("UPDATE `". DB_PREFIX . $this->db->escape(self::PRODUCT_TABLE) ."`
            SET `is_shuffled` = '". $this->db->escape($status) ."'
            WHERE `product_id` = '". $this->db->escape($product_id) ."'");
    }

    private function getProductsFromProductCategories($product_id, $most_close_only = true)
    {
        $categories = $this->getProductCategories($product_id);

        if ($most_close_only) {
            $categories = $this->getMostCloseCategories($categories);
        }

        return $this->getProductsForCategories($categories);
    }

    private function getMostCloseCategories($categories)
    {
        $filtered = array();
        $pathes = array();

        foreach ($categories as $cid) {
            $pathes[] = $this->getCategoryIdPath($cid);
        }

        // SORT BY PATH LENGTH
        usort($pathes, function($a, $b) {
            return strlen($b['path']) - strlen($a['path']);
        });

        foreach ($pathes as $k1 => $orig_pd) {
            foreach ($pathes as $k2 => $test_pd) {
                if ($k1 === $k2) { continue; }

                if (strpos($orig_pd['path'], $test_pd['path']) !== false) {
                    unset($pathes[$k2]);
                }
            }
        }

        $filtered = array_map(function($pd) {
            return $pd['category_id'];
        }, $pathes);

        return ($filtered) ?: $categories;
    }

    private function getCategoryIdPath($category_id)
    {
        $query = $this->db->query("SELECT cp.category_id, GROUP_CONCAT(cp.path_id ORDER BY level SEPARATOR '-') AS path
            FROM `". DB_PREFIX ."category_path` cp
            WHERE cp.category_id = '". (int)$category_id ."'
            GROUP BY cp.category_id");

        if (isset($query->row['path'])) {
            return $query->row;
        }
    }

    public function getProductCategories($product_id)
    {
        $categories = array();

        $query = $this->db->query("SELECT * FROM `". DB_PREFIX ."product_to_category`
            WHERE `product_id` = '" . (int)$product_id . "'");

        foreach ($query->rows as $result) {
            $categories[] = $result['category_id'];
        }

        return $categories;
    }

    private function getProductsForCategories($categories)
    {
        $products = array();

        if (is_array($categories) && $categories) {
            $sql = "SELECT DISTINCT p.product_id FROM `". DB_PREFIX ."product` p
                LEFT JOIN `". DB_PREFIX ."product_to_category` p2c ON (p.product_id = p2c.product_id)
                WHERE ";

                $keys = array_keys($categories);
                $last_element_key = array_pop($keys);

                foreach ($categories as $k => $category_id) {
                    $sql .= "(p2c.category_id = '" . (int)$category_id . "')";

                    if ($k != $last_element_key) {
                        $sql .= " OR ";
                    }
                }

            $query = $this->db->query($sql);

            foreach ($query->rows as $product) {
                $products[] = $product['product_id'];
            }
        }

        return $products;
    }

    private function clearRelatedForProduct($product_id)
    {
        $this->db->query("DELETE FROM `". DB_PREFIX ."product_related`
            WHERE `product_id` = '" . (int)$product_id . "'");
    }

    private function addRelatedForProduct($product_id, $related_id)
    {
        $this->db->query("INSERT INTO `". DB_PREFIX ."product_related`
            SET `product_id` = '" . (int)$product_id . "',
            `related_id` = '" . (int)$related_id . "'");
    }

    public function getLoadingProgress()
    {
        $result = array(
            'progress'  => 0,
            'message'   => '',
        );

        $key = md5(json_encode(array(__CLASS__, self::RESHUFFLE_CACHE)));
        $object = $this->pro_cache->getItem($key);

        if ($object->isHit()) {
            $data = $object->get();

            if ($data['products_count'] != 0) {
                $result['message'] = "{$data['processed_count']} / {$data['products_count']}";
                $result['progress'] = ($data['processed_count'] / $data['products_count']) * 100;
            }
        }

        return $result;
    }
}