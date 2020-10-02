<?php
/*
 *  location: catalog/model
 */
class ModelExtensionModuleOfferId extends Model
{
    private $codename = 'offer_id';
    private $route = 'extension/module/offer_id';

    const ID_TABLE = 'offer_export_id';

    public function __construct($registry)
    {
        parent::__construct($registry);
    }

    private function createTables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . self::ID_TABLE ."` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `importId` varchar(255) NOT NULL,
            `createDate` datetime NOT NULL,
            `updateDate` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE (`importId`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");
    }

    private function getId(string $importId)
    {
        $q = $this->db->query("SELECT *
            FROM `". DB_PREFIX . self::ID_TABLE . "`
            WHERE `importId` = '". $this->db->escape($importId) ."'");

        if (isset($q->row['id'])) {
            return (int) $q->row['id'];
        }
    }

    private function createId(string $importId)
    {
        $this->db->query("INSERT INTO `". DB_PREFIX . self::ID_TABLE ."`
            SET `importId` = '". $this->db->escape($importId) ."',
                `createDate` = NOW(),
                `updateDate` = NOW()");

        return $this->db->getLastId();
    }

    public function createAndReturnId(string $importId)
    {
        if (!trim($importId)) {
            return null;
        }
        $id = $this->getId($importId);
        if (!$id) {
            $id = $this->createId($importId);
        }
        return $id;
    }
}
