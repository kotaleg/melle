<?php
/*
 *  location: catalog/model
 */
class ModelExtensionModuleMelle extends Controller
{
    private $codename = 'melle';
    private $route = 'extension/module/melle';
    private $type = 'module';

    const ACTIVATION_TABLE = 'melle_customer_activation';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language($this->route);

        $this->load->model('extension/pro_patch/url');
        $this->load->model('extension/pro_patch/db');
        $this->load->model('extension/pro_patch/load');
    }

    public function renderOtherProducts($title, $products)
    {
        $data['title'] = $title;
        $data['products'] = $products;

        return $this->model_extension_pro_patch_load->view("{$this->route}/other_products", $data);
    }

    public function getMenu()
    {
        $menu = $this->cache->get('melle.menu');
        if ($menu) { return $menu; }

        $this->load->model('catalog/category');
        $top_categories = $this->model_catalog_category->getCategories(0);

        $menu = array();

        foreach ($top_categories as $cat) {

            if (!$cat['top']) { continue; }

            $children = array();
            $cc = $this->model_catalog_category->getCategories($cat['category_id']);
            foreach ($cc as $cat2) {
                $children[] = array(
                    'title'     => $cat2['name'],
                    'url'       => $this->model_extension_pro_patch_url->ajax('product/category', 'path=' . $cat['category_id'] .'_'. $cat2['category_id']),
                );
            }

            $cc_add = $this->model_catalog_category->getAdditionalCats($cat['category_id']);
            foreach ($cc_add as $cat3) {
                $check = true;
                foreach ($cc as $cat2) {
                    if ($cat3['category_id'] == $cat2['category_id']) {
                        $check = false;
                        break;
                    }
                }

                if ($check) {
                    $children[] = array(
                        'title'     => $cat3['name'],
                        'url'       => $this->model_extension_pro_patch_url->ajax('product/category', 'path=' . $cat['category_id'] .'_'. $cat3['category_id']),
                    );
                }
            }

            $menu[] = array(
                'title'     => $cat['name'],
                'url'       => $this->model_extension_pro_patch_url->ajax('product/category', 'path=' . $cat['category_id']),
                'children'  => $children,
                'active'    => false,
            );
        }

        $this->cache->set('melle.menu', $menu);
        return $menu;
    }

    private function createCustomerActivationTables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . $this->db->escape(self::ACTIVATION_TABLE) ."` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `customer_id` int(11) NOT NULL,
            `token` varchar(255) NOT NULL,
            `used` tinyint(3) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `no_duplicate` (`token`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");
    }

    private function getTokenForCustomer($customer_id)
    {
        $query = $this->db->query("SELECT *
            FROM `". DB_PREFIX . $this->db->escape(self::ACTIVATION_TABLE) . "`
            WHERE `customer_id` = '". (int)$customer_id ."'
            AND `used` = '". (int)false ."'");

        return $query->row['token'];
    }

    private function isUnusedToken($token)
    {
        $query = $this->db->query("SELECT *
            FROM `". DB_PREFIX . $this->db->escape(self::ACTIVATION_TABLE) . "`
            WHERE `token` = '". $this->db->escape($token) ."'
            AND `used` = '". (int)false ."'");

        return $query->row['customer_id'];
    }

    private function saveTokenForCustomer($customer_id, $token)
    {
        $this->db->query("INSERT INTO `". DB_PREFIX . $this->db->escape(self::ACTIVATION_TABLE) ."`
            SET `customer_id` = '" . (int)$customer_id . "',
                `token` = '" . $this->db->escape($token) . "',
                `used` = '". (int)false ."'");
    }

    private function updateTokenStatus($token, $status)
    {
        $this->db->query("UPDATE `". DB_PREFIX . $this->db->escape(self::ACTIVATION_TABLE) ."`
            SET `used` = '". (int)$status ."'
            WHERE `token` = '". $this->db->escape($token) ."'");
    }

    public function getActivationLinkForCustomer($customer_id)
    {
        if (!$this->model_extension_pro_patch_db->isTableExist(self::ACTIVATION_TABLE)) {
            $this->createCustomerActivationTables();
        }

        $token = $this->getTokenForCustomer($customer_id);
        if (!$token) {
            $token = sha1(microtime());
            $this->saveTokenForCustomer($customer_id, $token);
        }

        if ($token) {
            return $this->model_extension_pro_patch_url->ajax('account/account/activateAccount', "approveToken={$token}");
        }
    }

    public function activateToken($token)
    {
        if (!$this->model_extension_pro_patch_db->isTableExist(self::ACTIVATION_TABLE)) {
            $this->createCustomerActivationTables();
        }

        $customer_id = $this->isUnusedToken($token);
        if ($customer_id) {
            $this->updateTokenStatus($token, true);
            return $customer_id;
        }
    }

    public function getProductCategoriesWithPathAndType($productId)
    {
        return $this->db->query("SELECT ptc.category_id,
            (
                SELECT GROUP_CONCAT(cp.path_id
                ORDER BY level SEPARATOR '-')
                FROM `". DB_PREFIX ."category_path` cp
                WHERE cp.category_id = ptc.category_id
                GROUP BY cp.category_id
            ) AS `path`
            FROM `". DB_PREFIX ."product_to_category` ptc
            WHERE `product_id` = '" . (int) $productId . "'")->rows;
    }

    public function getCategoryType($categoryId)
    {
        $q = $this->db->query("SELECT c.category_type
            FROM `". DB_PREFIX ."category` c
            WHERE `category_id` = '" . (int) $categoryId . "'
            AND `status` = 1")->row;

        if (isset($q['category_type'])) {
            return $q['category_type'];
        }
    }

    public function getClosestCategoryTypeForProduct($productId)
    {
        $categories = $this->getProductCategoriesWithPathAndType($productId);

        // SORT BY PATH LENGTH
        usort($categories, function($a, $b) {
            return strlen($b['path']) - strlen($a['path']);
        });

        foreach ($categories as $key => $categoryData) {
            $pathParts = explode('-', $categoryData['path']);
            $pathParts = array_reverse($pathParts);

            foreach ($pathParts as $categoryId) {
                $categoryType = $this->getCategoryType($categoryId);
                if (utf8_strlen(trim($categoryType)) > 0) {
                    return $categoryType;
                }
            }
        }
    }

}
