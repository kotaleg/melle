<?php
/*
 *  location: admin/model
 */
class ModelExtensionModuleExtraDescription extends Model
{
    private $codename = 'extra_description';
    private $route = 'extension/module/extra_description';

    const DESCRIPTION_TABLE = 'product_extra_description';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/db');

        if (!$this->model_extension_pro_patch_db->isTableExist(self::DESCRIPTION_TABLE)) {
            $this->createTables();
        }
    }

    public function createTables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS 
        `". DB_PREFIX . $this->db->escape(self::DESCRIPTION_TABLE) ."` (
            
            `productId` int(11) NOT NULL,
            `languageId` int(11) NOT NULL,
            `description` TEXT NOT NULL,
            `createDate` datetime NOT NULL,
            `updateDate` datetime NOT NULL,

            PRIMARY KEY (`productId`,`languageId`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");
    }

    public function dropTables()
    {
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . $this->db->escape(self::DESCRIPTION_TABLE) ."`");
    }

    public function getDescriptions($productId)
    {
        $result = array();

        $rows = $this->db->query("SELECT * FROM `". DB_PREFIX . $this->db->escape(self::DESCRIPTION_TABLE) . "`
            WHERE `productId` = '". $this->db->escape($productId) ."'")->rows;

        if ($rows) {
            foreach ($rows as $row) {
                $result[$row['languageId']] = array(
                    'description' => $row['description'],
                    'createDate' => $row['createDate'],
                    'updateDate' => $row['updateDate'],
                );
            }
        }

        return $result;
    }

    public function saveDescription($productId, $data)
    {
        foreach ($data as $k => $v) {
            $description = isset($v['description']) ? $v['description'] : '';
            $this->deleteDescription($productId, $k);
            $this->setDescription($productId, $k, $description);
        }
    }

    private function setDescription($productId, $languageId, $description)
    {
        $this->db->query("INSERT INTO `". DB_PREFIX . $this->db->escape(self::DESCRIPTION_TABLE) ."`
            SET `productId` = '" . (int) $productId . "',
                `languageId` = '" . (int) $languageId . "',
                `description` = '" . $this->db->escape($description) . "',
                `createDate` = NOW(),
                `updateDate` = NOW()");
    }

    private function deleteDescription($productId, $languageId)
    {
        $this->db->query("DELETE FROM `". DB_PREFIX . $this->db->escape(self::DESCRIPTION_TABLE) ."`
            WHERE `productId` = '" . (int) $productId . "'
            AND `languageId` = '" . (int) $languageId . "'");
    }

    public function deleteDescriptionForProduct($productId)
    {
        $this->db->query("DELETE `". DB_PREFIX . $this->db->escape(self::DESCRIPTION_TABLE) ."`
            WHERE `productId` = '" . (int) $productId . "'");
    }
}