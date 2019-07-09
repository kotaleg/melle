<?php
/*
 *  location: admin/model
 */
class ModelExtensionModuleMelleBlocks extends Model
{
    private $codename = 'melle_blocks';
    private $route = 'extension/module/melle_blocks';

    const BLOCK_TABLE = 'melleb_block';

    const BTYPE_1 = 1;
    const BTYPE_2 = 2;
    const BTYPE_3 = 3;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/setting');
        $this->load->model('extension/pro_patch/module');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/db');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);
    }

    public function createTables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . self::BLOCK_TABLE ."` (
            `_id` int(11) NOT NULL AUTO_INCREMENT,

            `moduleId` int(11) NOT NULL,
            `type` int(3) NOT NULL,
            `link` varchar(255) NOT NULL,
            `image` varchar(255) NOT NULL,
            `text` varchar(255) NOT NULL,
            `buttonText` varchar(255) NOT NULL,
            `sortOrder` int(3) NOT NULL,
            `status` tinyint(1) NOT NULL,

            PRIMARY KEY (`_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");
    }

    public function dropTables()
    {
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . self::BLOCK_TABLE ."`");
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

    public function prepareItem($moduleId)
    {
        $item = array(
            'moduleId' => '',
            'name' => 'Кастомизируемый блок #',
            'height' => 400,
            'status' => false,
            'blocks' => false,
        );

        $item['name'] .= $this->getLastItemId();

        $moduleInfo = $this->model_extension_pro_patch_module->getModule($moduleId);
        if ($moduleInfo) {
            $item['moduleId'] = $moduleId;
            $item['name'] = $moduleInfo['name'];
            $item['height'] = $moduleInfo['height'];
            $item['status'] = $moduleInfo['status'];
        }


        // $this->load->model('tool/image');

        // if (isset($image['image']) && is_file(DIR_IMAGE . $image['image'])) {
        //     $image['thumb'] = $this->model_tool_image->resize($image['image'], 100, 100);
        // } else {
        //     $image['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        // }

        return $item;
    }

    private function getLastItemId()
    {
        $lastId = $this->model_extension_pro_patch_module->getLastIdByCode($this->codename);

        if ($lastId) { return $lastId; }

        return 1;
    }

    private function getBlocks($moduleId)
    {

    }

    public function getBlockTypes()
    {
        $types = array();
        $types[] = array(
            'type' => self::BTYPE_1,
            'typeDescription' => 'Обычный блок 25%',
            'typeWidth' => 25,

            'link' => '',
            'image' => '',
            'text' => '',
            'buttonText' => '',
            'sortOrder' => '',
        );
        $types[] = array(
            'type' => self::BTYPE_2,
            'typeDescription' => 'Широкий блок 50%',
            'typeWidth' => 50,

            'link' => '',
            'image' => '',
            'text' => '',
            'buttonText' => '',
            'sortOrder' => '',
        );
        $types[] = array(
            'type' => self::BTYPE_3,
            'typeDescription' => 'Мелкий блок 25%',
            'typeWidth' => 25,

            'link' => '',
            'image' => '',
            'text' => '',
            'sortOrder' => '',
        );

        return $types;
    }


    public function getPriceFile($name)
    {
        return $this->preparePriceFile($name);
    }

    private function preparePriceFile($name)
    {
        $file = $this->getPriceListByName($name);

        if ($file) {
            $file['status'] = (bool)$file['status'];
            return $file;
        } else {
            $this->initPriceList($name);
            return $this->preparePriceFile($name);
        }
    }

    private function getPriceListByName($name)
    {
        $q = $this->db->query("SELECT *
            FROM `". DB_PREFIX . $this->db->escape(self::PL_TABLE) . "`
            WHERE `filePath` = '" . $this->db->escape($name) . "'");

        return $q->row;
    }

    private function initRow($name)
    {
        $this->db->query("INSERT INTO `". DB_PREFIX . $this->db->escape(self::PL_TABLE) ."`
            SET `filePath` = '". $this->db->escape($name) ."',
                `title` = '". $this->db->escape($name) ."',
                `downloadCount` = '". (int)0 ."',
                `sortOrder` = '". (int)0 ."',
                `status` = '". (bool)0 ."'");
    }

    public function saveItem($data)
    {
        $json['saved'] = false;

        if ((utf8_strlen($data['name']) < 1) || (utf8_strlen($data['name']) > 32)) {
            $json['error'][] = 'Какое то хреновое имя';
        }

        if ((float)$data['height'] < 10) {
            $json['error'][] = 'Слишком маленькая высота';
        }

        if ((float)$data['widthCount'] != 100) {
            $json['error'][] = 'Ширина всех блоков должна быть равна 100%';
        }

        if (!isset($json['error'])) {

            // echo "<pre>"; print_r($data); echo "</pre>";exit;

            if (empty($data['moduleId'])) {
                $this->model_extension_pro_patch_module->addModule($this->codename, $data);
                $json['success'][] = 'Блок сохранен';
            } else {

                $moduleId = $data['moduleId'];
                unset($data['moduleId']);

                // save blocks
                unset($data['blocks']);

                $this->model_extension_pro_patch_module->editModule($moduleId, $data);
                $json['success'][] = 'Данные блока обновлены';
            }

            $json['saved'] = true;

        }

        return $json;
    }

    private function updateRowStatus($_id, $status)
    {
        $this->db->query("UPDATE `". DB_PREFIX . self::ROW_TABLE ."`
            SET `status` = '". (int)$status ."'
            WHERE `_id` = '" . (int)$_id . "'");
    }
}