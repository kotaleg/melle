<?php
/*
 *  location: admin/model
 */
class ModelExtensionModuleClosestIndex extends Model
{
    const INDEX_TABLE = 'closest_index';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/db');

        if (!$this->model_extension_pro_patch_db
            ->isTableExist(self::INDEX_TABLE)) {
            $this->createTables();
        }
    }

    public function getClosestSortOrder($productId)
    {
        $q = $this->db->query("SELECT `sortOrder`
            FROM `". DB_PREFIX . self::INDEX_TABLE ."` i
            WHERE i.productId = '". (int) $productId ."'");

        return (int) isset($q->row['sortOrder']) ? $q->row['sortOrder'] : 0;
    }

    public function isClosestSortOrder($productId)
    {
        $q = $this->db->query("SELECT `sortOrder`
            FROM `". DB_PREFIX . self::INDEX_TABLE ."` i
            WHERE i.productId = '". (int) $productId ."'");

        return (bool) ($q->num_rows) ? true : false;
    }

    public function setClosestSortOrder($productId, $sortOrder)
    {
        if ($this->isClosestSortOrder($productId)) {
            $this->db->query("UPDATE `". DB_PREFIX . self::INDEX_TABLE ."`
                SET `sortOrder` = '". (int) $sortOrder ."',
                    `updatedAt` = NOW()
                WHERE `productId` = '". (int) $productId ."'");
        } else {
            $this->db->query("INSERT INTO `". DB_PREFIX . self::INDEX_TABLE ."`
                SET `productId` = '". (int) $productId ."',
                    `sortOrder` = '". (int) $sortOrder ."',
                    `createdAt` = NOW(),
                    `updatedAt` = NOW()");
        }
    }

    public function setMainSortOrder($productId, $sortOrder)
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "product`
            SET `sort_order` = '" . (int) $sortOrder . "'
            WHERE `product_id` = '" . (int) $productId . "'");
    }

    private function createTables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . self::INDEX_TABLE ."` (
            `productId` int(11) NOT NULL,
            `sortOrder` int(11) NOT NULL,

            `createdAt` datetime NOT NULL,
            `updatedAt` datetime NOT NULL,

            PRIMARY KEY (`productId`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");
    }
}