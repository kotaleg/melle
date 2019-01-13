<?php
class ModelApiImport1CProducer extends Model
{
    private $codename = 'producer';
    private $route = 'api/import_1c/producer';

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
}