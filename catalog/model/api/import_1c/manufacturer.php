<?php
class ModelApiImport1CManufacturer extends Model
{
    private $codename = 'manufacturer';
    private $route = 'api/import_1c/manufacturer';

    const MANUFACTURER_TABLE = 'manufacturer';

    function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('api/import_1c/helper');
    }

    public function action($parsed)
    {
        if (isset($parsed->classificator->producers)
            && is_array($parsed->classificator->producers)) {

            foreach ($parsed->classificator->producers as $k => $producer) {
                if (!$this->model_api_import_1c_helper->isImportRecordExist(self::MANUFACTURER_TABLE, $producer->id)) {
                    $this->addManufacturer(array(
                        'name'          => $producer->name,
                        'import_id'     => $producer->id,
                        'sort_order'    => $k,
                        'manufacturer_store' => array(
                            0 => $this->config->get('config_store_id'),
                        ),
                    ));
                } else {
                    $this->editManufacturer($producer->id, array(
                        'name'          => $producer->name,
                        'sort_order'    => $k,
                        'manufacturer_store' => array(
                            0 => $this->config->get('config_store_id'),
                        ),
                    ));
                }
            }

            $this->cache->delete('manufacturer');
        }
    }

    private function addManufacturer($data)
    {
        $this->db->query("INSERT INTO `". DB_PREFIX ."manufacturer`
            SET `name` = '". $this->db->escape($data['name']) ."',
                `import_id` = '". $this->db->escape($data['import_id']) ."',
                `sort_order` = '". (int)$data['sort_order'] ."'");

        $manufacturer_id = $this->db->getLastId();

        if (isset($data['manufacturer_store'])) {
            foreach ($data['manufacturer_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store
                    SET manufacturer_id = '" . (int)$manufacturer_id . "',
                        store_id = '" . (int)$store_id . "'");
            }
        }
    }

    public function getManufacturerByImportId($import_id)
    {
        $q = $this->db->query("SELECT `manufacturer_id` FROM `". DB_PREFIX ."manufacturer`
            WHERE `import_id` = '". $this->db->escape($import_id) ."'");

        if (isset($q->row['manufacturer_id'])) {
            return $q->row['manufacturer_id'];
        }
    }

    private function editManufacturer($import_id, $data)
    {
        $manufacturer_id = $this->getManufacturerByImportId($import_id);

        $this->db->query("UPDATE `". DB_PREFIX ."manufacturer`
            SET `name` = '". $this->db->escape($data['name']) ."',
                `sort_order` = '". (int)$data['sort_order'] ."'
            WHERE `manufacturer_id` = '". (int)$manufacturer_id ."'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "manufacturer_to_store
            WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

        if (isset($data['manufacturer_store'])) {
            foreach ($data['manufacturer_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store
                    SET manufacturer_id = '" . (int)$manufacturer_id . "',
                    store_id = '" . (int)$store_id . "'");
            }
        }
    }
}