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
                    ));
                } else {
                    $this->editManufacturer($producer->id, array(
                        'name'          => $producer->name,
                        'sort_order'    => $k,
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
    }

    private function editManufacturer($import_id, $data)
    {
        $this->db->query("UPDATE `". DB_PREFIX ."manufacturer`
            SET `name` = '". $this->db->escape($data['name']) ."',
                `sort_order` = '". (int)$data['sort_order'] ."'
            WHERE `import_id` = '". $this->db->escape($import_id) ."'");
    }
}