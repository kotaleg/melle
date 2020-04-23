<?php
class ModelApiImportMelle extends Model
{
    private $codename = 'import_melle';
    private $route = 'api/import_melle';
    private $setting_route = 'extension/module/import_melle';

    private $exchange_path;

    // STORE TABLES
    const MANUFACTURER_ALT_NAME_TABLE = 'manufacturer_alt_name';

    function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/db');
        $this->load->model('api/import_1c/progress');

        $this->exchange_path = $this->getRootPath() . 'protected/runtime/exchange/';
    }

    private function getRootPath()
    {
        return dirname(DIR_SYSTEM).'/';
    }

    // ManufacturerAltName
    public function actionManImport($filename)
    {
        $json = array();

        if (empty($filename)) {
            $filename = 'manufacturer-alt-name.xlsx';
        }

        $excelFile = "{$this->exchange_path}{$filename}";

        if (!is_file($excelFile) || !is_readable($excelFile)) {
            $json['error'][] = "`{$filename}` не существует";
            $json['success'] = false;
            return $json;
        }

        if (!$this->model_extension_pro_patch_db
            ->isTableExist(self::MANUFACTURER_ALT_NAME_TABLE)) {
            $this->createTablesForMan();
        }

        try {
            
            $data = $this->getDataFromManFile($excelFile);
            foreach ($data as $importId => $value) {
                $manufacturerData = $this->getManufacturerByImportId($importId);
                if (isset($manufacturerData['manufacturer_id'])) {
                    if (strcmp(utf8_strtolower($manufacturerData['name']), utf8_strtolower($value['name'])) !== 0) {
                        $this->updateManufacturerName($manufacturerData['manufacturer_id'], $value['name']);
                    }

                    $this->deleteAltNameForManufacturer($manufacturerData['manufacturer_id']);
                    foreach ($value['altNames'] as $altName) {
                        $this->setAltNameForManufacturer($manufacturerData['manufacturer_id'], $altName);
                    }
                }
            }

            $json['success'] = true;

        } catch (\Exception $e) {
            $this->log->write(json_encode($e));
            $json['success'] = false;
            $json['error'][] = 'Ошибка при обработке файла';
        }

        return $json;
    }

    private function createTablesForMan()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS 
        `". DB_PREFIX . $this->db->escape(self::MANUFACTURER_ALT_NAME_TABLE) ."` (
            `manufacturerId` int(11) NOT NULL,
            `altName` varchar(255) NOT NULL,
            `createDate` datetime NOT NULL
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");
    }

    private function deleteAltNameForManufacturer($manufacturerId)
    {
        $this->db->query("DELETE FROM `". DB_PREFIX . $this->db->escape(self::MANUFACTURER_ALT_NAME_TABLE) ."`
            WHERE `manufacturerId` = '" . (int) $manufacturerId . "'");
    }

    private function setAltNameForManufacturer($manufacturerId, $name)
    {
        $this->db->query("INSERT INTO `". DB_PREFIX . $this->db->escape(self::MANUFACTURER_ALT_NAME_TABLE) ."`
            SET `manufacturerId` = '" . (int) $manufacturerId . "',
                `altName` = '". $this->db->escape($name) ."',
                `createDate` = NOW()");
    }

    private function getManufacturerByImportId($importId)
    {
        return $this->db->query("SELECT * FROM `". DB_PREFIX ."manufacturer`
            WHERE `import_id` = '" . $this->db->escape($importId)  . "'")->row;
    }

    private function updateManufacturerName($manufacturerId, $name)
    {
        return $this->db->query("UPDATE `". DB_PREFIX ."manufacturer`
            SET `name` = '". $this->db->escape($name) ."'
            WHERE `manufacturer_id` = '" . (int) $manufacturerId . "'")->row;
    }

    private function getDataFromManFile($excelFile)
    {
        $data = array();

        $reader = \pro_spreadsheet\reader::createReaderForFile($excelFile);
        $reader->setReadDataOnly(true);
        
        $spreadsheet = $reader->load($excelFile);
        $worksheet = $spreadsheet->getActiveSheet();

        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $dataItem = array(
                'importId' => false,
                'name' => false,
                'altNames' => array(),
            );

            foreach ($cellIterator as $cell) {
                switch($cell->getColumn()) {
                case 'A':
                    $dataItem['importId'] = (string) $cell->getValue();
                    break;
                case 'B':
                    $dataItem['name'] = (string) $cell->getValue();
                    break;
                default:
                    $cellValue = (string) $cell->getValue();
                    if (trim(utf8_strlen($cellValue)) > 0) {
                        $dataItem['altNames'][] = $cellValue;
                    }
                    break;
                }
            }

            if (utf8_strlen($dataItem['importId']) === 36) {
                $data[$dataItem['importId']] = $dataItem;
            }
        }

        return $data;
    }

    
}