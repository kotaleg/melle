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

}
