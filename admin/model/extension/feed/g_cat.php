<?php
/*
 *  location: admin/model
 */
class ModelExtensionFeedGCat extends Model
{
    private $codename = 'g_cat';
    private $route = 'extension/feed/g_cat';

    const G_CATEGORY = 'gcat_category';
    const G_CATEGORY_DESCRIPTION = 'gcat_category_description';
    const G_CONNECTION = 'gcat_connection';


    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/setting');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/db');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);
        $this->g_cat = new \g_cat\g_cat($this->setting);
    }

    public function createTables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . self::G_CATEGORY ."` (
            `_id` int(11) NOT NULL AUTO_INCREMENT,

            `categoryId` int(11) NOT NULL,
            `parentId` int(11) NOT NULL,

            `sortOrder` tinyint(1) NOT NULL,
            `status` tinyint(1) NOT NULL,

            PRIMARY KEY (`_id`),
            UNIQUE KEY `no_duplicate` (`categoryId`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . self::G_CATEGORY_DESCRIPTION ."` (
            `_id` int(11) NOT NULL AUTO_INCREMENT,

            `categoryId` int(11) NOT NULL,
            `languageCode` char(16) NOT NULL,

            `title` varchar(255) NOT NULL,

            PRIMARY KEY (`_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . self::G_CONNECTION ."` (
            `_id` int(11) NOT NULL AUTO_INCREMENT,

            `categoryId` int(11) NOT NULL,
            `storeCategoryId` int(11) NOT NULL,

            PRIMARY KEY (`_id`),
            UNIQUE KEY `no_duplicate` (`storeCategoryId`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");
    }

    public function dropTables()
    {
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . self::G_CATEGORY ."`");
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . self::G_CATEGORY_DESCRIPTION ."`");
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . self::G_CONNECTION ."`");
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

    public function getLanguages()
    {
        return array_map(function($v) {
            return array(
                'code' => $v,
                'selected' => (bool) (strcmp($v, $this->setting['languageCode']) === 0)
            );
        }, $this->g_cat->getLanguageCodes());
    }

    public function updateCategories()
    {
        $this->clearCategories();

        $categories = $this->g_cat->getGoogleProductCategories();

        foreach ($categories as $c) {
            $this->addCategory($c);
        }

        return $this->getCategoriesCount();
    }

    private function clearCategories()
    {
        $this->db->query("TRUNCATE TABLE `". DB_PREFIX . self::G_CATEGORY ."`");
        $this->db->query("TRUNCATE TABLE `". DB_PREFIX . self::G_CATEGORY_DESCRIPTION ."`");
    }

    public function clearConnections()
    {
        $this->db->query("TRUNCATE TABLE `". DB_PREFIX . self::G_CONNECTION ."`");
    }

    private function addCategory($data)
    {
        $this->db->query("INSERT INTO `". DB_PREFIX . self::G_CATEGORY ."`
            SET `categoryId` = '". (int)$data['categoryId'] ."',
                `parentId` = '". (int)$data['parentId'] ."',
                `sortOrder` = '". (int)0 ."',
                `status` = '". (bool)0 ."'");

        $this->db->query("INSERT INTO `". DB_PREFIX . self::G_CATEGORY_DESCRIPTION ."`
            SET `categoryId` = '". (int)$data['categoryId'] ."',
                `languageCode` = '". $this->db->escape($this->setting['languageCode']) ."',
                `title` = '". $this->db->escape($data['title']) ."'");
    }

    public function getCategoriesCount()
    {
        $q = $this->db->query("SELECT count(c._id) as total
            FROM `". DB_PREFIX . self::G_CATEGORY . "` c
            LEFT JOIN `". DB_PREFIX . self::G_CATEGORY_DESCRIPTION . "` cd
            ON (c.categoryId = cd.categoryId)
            WHERE cd.languageCode = '" . $this->db->escape($this->setting['languageCode']) . "'");

        return (int) $q->row['total'];
    }

    public function getUnlinkedCount()
    {
        $q = $this->db->query("SELECT count(c.category_id) -
            (SELECT count(c.category_id)
                FROM " . DB_PREFIX . "category c
                LEFT JOIN `". DB_PREFIX . self::G_CONNECTION . "` g
                ON (g.storeCategoryId = c.category_id)
                WHERE g.categoryId IS NOT NULL)
            as total FROM " . DB_PREFIX . "category c");

        return (int) $q->row['total'];
    }

    public function prepareStoreCategories($parentId = 0)
    {
        $result = array();
        $refs = array();

        foreach ($this->getStoreCategories() as $cat) {
            $thisref = &$refs[ $cat['id'] ];

            $postfix = ($cat['gCategory']) ? " ({$cat['gCategory']})" : '';

            $thisref['id'] = $cat['id'];
            $thisref['label'] = "{$cat['name']}{$postfix}";

            if ($cat['parent_id'] == $parentId) {
                $result[] = &$thisref;
            } else {
                $refs[ $cat['parent_id'] ]['children'][] = &$thisref;
            }
        }

        return $result;
    }

    private function getStoreCategories($parentId = null)
    {
        $sql = "SELECT g.categoryId as gCategory, c.category_id as id, c.parent_id, cd.name
            FROM " . DB_PREFIX . "category c
            LEFT JOIN " . DB_PREFIX . "category_description cd
            ON (c.category_id = cd.category_id)
            LEFT JOIN `". DB_PREFIX . self::G_CONNECTION . "` g
            ON (g.storeCategoryId = c.category_id)
            WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if ($parentId !== null) {
            $sql .= " AND c.parent_id = '". (int)$parentId ."' ";
        }

        $q = $this->db->query($sql);
        return $q->rows;
    }

    public function prepareCategories()
    {
        $parentId = 0;
        $result = array();
        $refs = array();

        foreach ($this->getCategories() as $cat) {
            $thisref = &$refs[ $cat['categoryId'] ];

            $thisref['id'] = $cat['categoryId'];
            $thisref['label'] = "{$cat['title']} ({$cat['categoryId']})";

            if ($cat['parentId'] == $parentId) {
                $result[] = &$thisref;
            } else {
                $refs[ $cat['parentId'] ]['children'][] = &$thisref;
            }
        }

        return $result;
    }

    private function getCategories()
    {
        $q = $this->db->query("SELECT *
            FROM `". DB_PREFIX . self::G_CATEGORY . "` c
            LEFT JOIN `". DB_PREFIX . self::G_CATEGORY_DESCRIPTION . "` cd
            ON (c.categoryId = cd.categoryId)
            WHERE cd.languageCode = '" . $this->db->escape($this->setting['languageCode']) . "'");

        return $q->rows;
    }

    public function applyLink($selectedStoreCategories, $selectedCategory)
    {
        $updated = false;

        if (!$selectedStoreCategories) { return $updated; }
        if (!$selectedCategory) { return $updated; }

        $updated = true;

        $selected = array();

        foreach ($selectedStoreCategories as $categoryId) {

            $s = array_map(function($v) {
                return $v['id'];
            }, $this->getStoreCategories($categoryId));

            $selected += $s;
            $selected[] = $categoryId;
        }

        $selected = array_unique($selected, SORT_NUMERIC);

        foreach ($selected as $categoryId) {
            $this->applyCategoryToStoreCategory($categoryId, $selectedCategory);
        }

        return $updated;
    }

    private function applyCategoryToStoreCategory($storeCategoryId, $categoryId)
    {
        $sql = $this->model_extension_pro_patch_db->sqlOnDuplicateUpdateBuilder(
        self::G_CONNECTION,
        array(
            'storeCategoryId' => array(
                'update' => false,
                'data' => $storeCategoryId,
            ),
            'categoryId' => $categoryId,
        ));

        $this->db->query($sql);
    }

    private function updatePriceListStatus($_id, $status)
    {
        $this->db->query("UPDATE `". DB_PREFIX . self::PL_TABLE ."`
            SET `status` = '". (int)$status ."'
            WHERE `_id` = '" . (int)$_id . "'");
    }
}