<?php
/*
 *  location: admin/model
 */
class ModelExtensionModuleMelleBlocks extends Model
{
    private $codename = 'melle_blocks';
    private $route = 'extension/module/melle_blocks';

    private $setting = array();

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
        $this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX . \melle_blocks\constant::BLOCK_TABLE ."` (
            `_id` int(11) NOT NULL AUTO_INCREMENT,

            `moduleId` int(11) NOT NULL,
            `type` char(16) NOT NULL,
            `link` varchar(255) NOT NULL,
            `image` varchar(255) NOT NULL,
            `text` varchar(255) NOT NULL,
            `buttonText` varchar(255) NOT NULL,
            `buttonColor` varchar(255) NOT NULL,
            `backgroundColor` varchar(16) NOT NULL,
            `sortOrder` int(3) NOT NULL,
            `status` tinyint(1) NOT NULL,

            PRIMARY KEY (`_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=MyISAM;");
    }

    public function dropTables()
    {
        $this->db->query("DROP TABLE IF EXISTS `". DB_PREFIX . \melle_blocks\constant::BLOCK_TABLE ."`");
    }

    public function getScriptFiles()
    {
        if (isset($this->setting['debug']) && $this->setting['debug']) {
            $rand = '?'.rand(777, 999);
        } else { $rand = ''; }

        $scripts = array();
        $scripts[] = "view/javascript/{$this->codename}/dist/main.js{$rand}";

        return $scripts;
    }

    public function prepareWidthTypes()
    {
        return array_map(function($item) {
            return array(
                'id' => $item,
                'label' => "{$item}/12",
            );
        }, \melle_blocks\constant::WIDTH_TYPES);
    }

    public function prepareButtonTypes()
    {
        return array_map(function($item) {
            return array(
                'id' => $item,
                'label' => $item,
            );
        }, \melle_blocks\constant::BUTTON_TYPES);
    }

    public function prepareItem($moduleId)
    {
        $item = array(
            'moduleId' => '',
            'name' => 'Кастомизируемый блок #',
            'height' => 400,
            'width' => 12,
            'backgroundColor' => '#FFFFFF',
            'status' => false,
            'blocks' => false,
        );

        $item['name'] .= $this->getLastItemId();

        $moduleInfo = $this->model_extension_pro_patch_module->getModule($moduleId);
        if ($moduleInfo) {
            $item['moduleId'] = $moduleId;
            $item['name'] = $moduleInfo['name'];
            $item['height'] = $moduleInfo['height'];
            $item['width'] = $moduleInfo['width'];
            $item['status'] = $moduleInfo['status'];
            if ($moduleInfo['backgroundColor']) {
                $item['backgroundColor'] = $moduleInfo['backgroundColor'];
            }
        }

        return $item;
    }

    private function getLastItemId()
    {
        $lastId = $this->model_extension_pro_patch_module->getLastIdByCode($this->codename);

        if ($lastId) { return $lastId; }

        return 1;
    }

    public function prepareBlocks($moduleId)
    {
        $blocks = array();

        $this->load->model('tool/image');

        foreach ($this->getBlocks($moduleId) as $b) {
            $bt = $this->getBlockType($b['type']);
            if (!$bt) { continue; }

            foreach ($bt as $k => $v) {
                if (array_key_exists($k, $b)) {
                    $bt[$k] = $b[$k];
                }
            }

            if (isset($bt['image']) && is_file(DIR_IMAGE . $bt['image'])) {
                $bt['thumb'] = $this->model_tool_image->resize($bt['image'], 100, 100);
            } else {
                $bt['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
            }

            $blocks[] = $bt;
        }

        return $blocks;
    }

    private function getBlocks($moduleId)
    {
        $q = $this->db->query("SELECT *
            FROM `". DB_PREFIX . \melle_blocks\constant::BLOCK_TABLE ."`
            WHERE `moduleId` = '" . (int)$moduleId . "'
            ORDER BY `sortOrder` ASC");

        return $q->rows;
    }

    public function getBlockTypes()
    {
        $this->load->model('tool/image');

        $types = array();
        $types[] = array(
            'type' => \melle_blocks\constant::BTYPE_1,
            'typeDescription' => 'Картинка с подписью 25%',
            'typeWidth' => 25,

            'link' => '',
            'image' => '',
            'thumb' => $this->model_tool_image->resize('no_image.png', 100, 100),
            'text' => '',
            'buttonText' => '',
            'buttonColor' => 'success',
            'sortOrder' => 1,
        );
        $types[] = array(
            'type' => \melle_blocks\constant::BTYPE_2,
            'typeDescription' => 'Широкий блок с текстом 100%',
            'typeWidth' => 100,

            'link' => '',
            'image' => '',
            'thumb' => $this->model_tool_image->resize('no_image.png', 100, 100),
            'text' => '',
            'buttonText' => '',
            'buttonColor' => 'success',
            'sortOrder' => 1,
        );
        $types[] = array(
            'type' => \melle_blocks\constant::BTYPE_3,
            'typeDescription' => 'Картинка без подписи 50%',
            'typeWidth' => 50,

            'link' => '',
            'image' => '',
            'thumb' => $this->model_tool_image->resize('no_image.png', 100, 100),
            'text' => '',
            'buttonText' => '',
            'buttonColor' => 'success',
            'sortOrder' => 1,
        );
        $types[] = array(
            'type' => \melle_blocks\constant::BTYPE_4,
            'typeDescription' => 'Картинка без подписи 33.3%',
            'typeWidth' => 33.3333333333333,

            'link' => '',
            'image' => '',
            'thumb' => $this->model_tool_image->resize('no_image.png', 100, 100),
            'text' => '',
            'buttonText' => '',
            'buttonColor' => 'success',
            'sortOrder' => 1,
        );

        return $types;
    }

    public function getBlockType($type)
    {
        foreach ($this->getBlockTypes() as $bt) {
            if (strcmp($bt['type'], $type) === 0) {
                return $bt;
            }
        }
    }

    public function saveItem($data)
    {
        $json['saved'] = false;

        if ((utf8_strlen($data['name']) < 1) || (utf8_strlen($data['name']) > 32)) {
            $json['error'][] = 'Какое то хреновое имя';
        }

        if (!in_array($data['width'], \melle_blocks\constant::WIDTH_TYPES)) {
            $json['error'][] = 'Некоректно заданна шрина';
        }

        if ((float)$data['height'] < 10) {
            $json['error'][] = 'Слишком маленькая высота';
        }

        if (isset($data['backgroundColor']['hex'])) {
            $data['backgroundColor'] = $data['backgroundColor']['hex'];
        }

        if (utf8_strlen($data['backgroundColor']) <= 1) {
            $json['error'][] = 'Не очень похоже на корректный цвет';
        }

        if ((float)$this->countBlocksWidth($data['blocks']) != 100) {
            $json['error'][] = 'Ширина всех блоков должна быть равна 100%';
        }

        if (!isset($json['error'])) {

            $moduleId = $data['moduleId'];

            // remove blocks
            $this->removeBlocks($moduleId);

            // remove unused blocks
            $this->removeUnusedBlocks();

            // parse blocks
            $blocks = $this->parseBlocks($data['blocks'], $data['extra']);
            unset($data['blocks'], $data['extra']);

            if (empty($moduleId)) {
                $moduleId = $this->model_extension_pro_patch_module->addModule($this->codename, $data);
                $json['success'][] = 'Блок сохранен';
            } else {
                $this->model_extension_pro_patch_module->editModule($moduleId, $data);
                $json['success'][] = 'Данные блока обновлены';
            }

            // save blocks
            $this->saveBlocks($moduleId, $blocks);

            $json['moduleId'] = $moduleId;
            $json['saved'] = true;

            $this->cache->delete('melle.melle_blocks');
        }

        return $json;
    }

    public function countBlocksWidth($blocks)
    {
        $widthCount = 0;

        foreach ($blocks as $b) {
            if (isset($b['type'])) {
                $bt = $this->getBlockType($b['type']);
                if ($bt && isset($bt['typeWidth'])) {
                    $widthCount += $bt['typeWidth'];
                }
            }
        }

        return round($widthCount, 2);
    }

    private function removeBlocks($moduleId)
    {
        $this->db->query("DELETE FROM `". DB_PREFIX . \melle_blocks\constant::BLOCK_TABLE ."`
            WHERE `moduleId` = '" . (int)$moduleId . "'");

        $this->cache->delete('melle.melle_blocks');
    }

    private function removeUnusedBlocks()
    {
        $usedIds = array_map(function($v) {
            return $v['module_id'];
        }, $this->model_extension_pro_patch_module->getModulesByCode($this->codename));

        if ($usedIds) {
            $ids = $this->model_extension_pro_patch_db->prepareSqlParents($usedIds);

            $this->db->query("DELETE FROM `". DB_PREFIX . \melle_blocks\constant::BLOCK_TABLE ."`
                WHERE `moduleId` NOT IN (" . $ids . ")");
        }

        $this->cache->delete('melle.melle_blocks');
    }

    private function parseBlocks($blocks, $extra)
    {
        foreach ($blocks as $k => $v) {
            if (array_key_exists("image-{$k}", $extra)) {
                $blocks[$k]['image'] = $extra["image-{$k}"];
            }
        }

        return $blocks;
    }

    private function saveBlocks($moduleId, $blocks)
    {
        foreach ($blocks as $b) {
            $this->saveBlock($moduleId, $b);
        }
    }

    private function saveBlock($moduleId, $block)
    {
        $this->db->query("INSERT INTO `". DB_PREFIX . \melle_blocks\constant::BLOCK_TABLE ."`
            SET `moduleId` = '". (int)$moduleId ."',
                `type` = '". $this->db->escape($block['type']) ."',
                `link` = '". $this->db->escape($block['link']) ."',
                `image` = '". $this->db->escape($block['image']) ."',
                `text` = '". $this->db->escape($block['text']) ."',
                `buttonText` = '". $this->db->escape($block['buttonText']) ."',
                `buttonColor` = '". $this->db->escape($block['buttonColor']) ."',
                `sortOrder` = '". (int)$block['sortOrder'] ."',
                `status` = '". (bool)true ."'");
    }
}
