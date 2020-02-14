<?php
/*
 *  location: admin/model
 */
class ModelExtensionModuleCatalogOptionOption extends Model
{
    private $codename = 'catalog_option';
    private $route = 'extension/module/catalog_option';

    private $localCodename = 'option';

    private $catalogOptionRoute = 'catalog/option';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language($this->catalogOptionRoute);
        $this->load->language($this->localRoute);

        $this->load->model('extension/pro_patch/setting');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/db');
        $this->load->model('extension/pro_patch/url');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);
    }

    public function getComplexCatalogOptionsTotal($data = array())
    {
        $sql = "SELECT COUNT(o.option_id) as `total` FROM `" . DB_PREFIX . "option` o
            LEFT JOIN " . DB_PREFIX . "option_description od
            ON (o.option_id = od.option_id)
            WHERE od.language_id = '" . (int) $this->config->get('config_language_id') . "'";

        $q = $this->db->query($sql);
        return (int) $q->row['total'];
    }

    public function getComplexCatalogOptions($data = array())
    {
        $sql = "SELECT o.option_id, o.sort_order, od.name, od.language_id
            FROM `" . DB_PREFIX . "option` o
            LEFT JOIN " . DB_PREFIX . "option_description od
            ON (o.option_id = od.option_id)
            WHERE od.language_id = '" . (int) $this->config->get('config_language_id') . "'";


        $sql .= " ORDER BY od.name DESC ";

        // @todo add sort by name, sort_order

        if (isset($data['start']) || isset($data['limit'])) {

            if ($data['limit'] != -1) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = (int) $this->config->get('config_limit_admin');
                }

                $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
            }
        }

        return $this->db->query($sql)->rows;
    }

    public function prepareComplexCatalogOptions($data)
    {
        return array_map(function($v) {
            return array(
                'optionId' => (int) $v['option_id'],
                'name' => (string) html_entity_decode($v['name']),
                'languageId' => (int) $v['language_id'],
                'sortOrder' => (int) $v['sort_order'],
            );
        }, $this->getComplexCatalogOptions($data));
    }

    public function getCatalogOptionData($optionId)
    {
        $option = array(
            'optionId' => $optionId,
            'type' => null,
            'sortOrder' => 0,
            'names' => array(),
        );

        $optionData = $this->getCatalogOptionById($optionId);
        if (isset($optionData['option_id'])) {
            $option['type'] = (string) $optionData['type'];
            $option['sortOrder'] = (int) $optionData['sort_order'];
        }

        foreach ($this->getLanguages() as $language) {
            $option['names'][$language['languageId']] = array(
                'languageId' => $language['languageId'],
                'languageCode' => $language['code'],
                'languageImage' => "language/{$language['code']}/{$language['code']}.png",
                'languageName' => $language['name'],

                'content' => $this->getCatalogOptionName($optionId, $language['languageId']),
            );
        }

        return $option;
    }

    public function getCatalogOptionById($optionId)
    {
        return $this->db->query("SELECT *
            FROM `". DB_PREFIX ."option`
            WHERE `option_id` = '". (int) $optionId ."'")->row;
    }

    private function getCatalogOptionName($optionId, $languageId)
    {
        $q = $this->db->query("SELECT `name`
            FROM `". DB_PREFIX ."option_description`
            WHERE `option_id` = '". (int) $optionId ."'
            AND `language_id` = '". (int) $languageId ."'");

        if (isset($q->row['name'])) {
            return $q->row['name'];
        }
        return '';
    }

    private function getLanguages()
    {
        $q = $this->db->query("SELECT * FROM `". DB_PREFIX . "language`
            ORDER BY sort_order, name");

        return array_map(function($v) {
            return array(
                'languageId' => (int) $v['language_id'],
                'code' => $v['code'],
                'name' => $v['name'],
            );
        }, $q->rows);
    }

    public function prepareOptionTypes()
    {
        $types = array(
            'select',
            'radio',
            'checkbox',
            'text',
            'textarea',
            'file',
            'date',
            'time',
            'datetime',
        );

        return array_map(function($type) {
            return array(
                'id' => $type,
                'label' => html_entity_decode($this->language->get("text_{$type}")),
            );
        }, $types);
    }

    public function getOptionTypesRequireValues()
    {
        return array(
            'select',
            'radio',
            'checkbox',
        );
    }

    public function saveCatalogOption($data)
    {
        $json['saved'] = false;

        foreach ($data['names'] as $nameData) {
            if (empty($nameData['content'])) {
                $json['error'][] = sprintf(
                    $this->language->get('error_catalog_option_name'), 
                    $nameData['languageName']);
            }
        }

        if (!isset($data['type'])) {
            $json['error'][] = $this->language->get('error_catalog_option_type');
        }

        // @todo find a way to properly save option_values before creating option
        // if (in_array($data['type'], $this->getOptionTypesRequireValues())
        // && $this->getComplexCatalogOptionValuesTotal(array('optionId' => $data['optionId'])) <= 0) {
        //     $json['error'][] = $this->language->get('error_type');
        // }

        if (!isset($data['sortOrder'])) {
            $json['error'][] = $this->language->get('error_catalog_option_sort_order');
        }

        if (!isset($json['error'])) {

            if (!$data['optionId']) {

                $data['optionId'] = $this->addCatalogOption($data);
                $this->editCatalogOptionName($data['optionId'], $data['names']);

                $json['success'][] = sprintf(
                    $this->language->get('success_catalog_option_created'), 
                    $data['optionId']);

            } else {
                
                $this->editCatalogOption($data['optionId'], $data);
                $this->editCatalogOptionName($data['optionId'], $data['names']);

                if (!in_array($data['type'], $this->getOptionTypesRequireValues())) {
                    $this->deleteCatalogOptionValuesForOptionId($data['optionId']);
                }

                $json['success'][] = sprintf(
                    $this->language->get('success_catalog_option_updated'), 
                    $data['optionId']);
            }

            $json['saved'] = true;
        }

        $json['optionId'] = $data['optionId'];
        return $json;
    }

    public function addCatalogOption($data)
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "option` 
            SET `type` = '" . $this->db->escape($data['type']) . "', 
                `sort_order` = '" . (int) $data['sortOrder'] . "'");
        
        return $this->db->getLastId();
    }

    public function editCatalogOption($optionId, $data)
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "option` 
            SET `type` = '" . $this->db->escape($data['type']) . "', 
                `sort_order` = '" . (int) $data['sortOrder'] . "' 
            WHERE `option_id` = '" . (int) $optionId . "'");
    }

    public function editCatalogOptionName($optionId, $names)
    {
        $this->db->query("DELETE FROM `". DB_PREFIX ."option_description`
            WHERE `option_id` = '" . (int) $optionId . "'");

        foreach ($names as $languageId => $value) {
            $this->db->query("INSERT INTO `". DB_PREFIX ."option_description`
                SET `option_id` = '" . (int) $optionId . "',
                    `language_id` = '" . (int) $languageId . "',
                    `name` = '" . $this->db->escape($value['content']) . "'");
        }
    }

    public function deleteCatalogOption($optionId)
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "option`
            WHERE `option_id` = '" . (int) $optionId . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "option_description`
            WHERE `option_id` = '" . (int) $optionId . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "option_value`
            WHERE `option_id` = '" . (int) $optionId . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "option_value_description`
            WHERE `option_id` = '" . (int) $optionId . "'");
    }

    /* OPTION VALUES START */
    public function getComplexCatalogOptionValuesTotal($data = array())
    {
        $sql = "SELECT COUNT(ov.option_value_id) as `total` FROM " . DB_PREFIX . "option_value ov 
            LEFT JOIN " . DB_PREFIX . "option_value_description ovd 
            ON (ov.option_value_id = ovd.option_value_id) 
            WHERE ov.option_id = '" . (int) $data['optionId'] . "' 
            AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (isset($data['searchTerm']) && $data['searchTerm'] !== false) {
            $sql .= " AND ovd.name LIKE '%". $this->db->escape($data['searchTerm']) ."%' ";
        }

        return (int) $this->db->query($sql)->row['total'];
    }

    public function getComplexCatalogOptionValues($data = array())
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "option_value ov 
            LEFT JOIN " . DB_PREFIX . "option_value_description ovd 
            ON (ov.option_value_id = ovd.option_value_id) 
            WHERE ov.option_id = '" . (int) $data['optionId'] . "' 
            AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (isset($data['searchTerm']) && $data['searchTerm'] !== false) {
            $sql .= " AND ovd.name LIKE '%". $this->db->escape($data['searchTerm']) ."%' ";
        }

        if (!empty($data['sortData']) && is_array($data['sortData'])) {

            $sql .= " ORDER BY ";

            $sortCount = count($data['sortData']);
            foreach ($data['sortData'] as $i => $sort) {
                if (isset($sort['field']) && isset($sort['type'])) {

                    switch ($sort['field']) {
                        case 'sortOrder':
                            $sql .= " ov.sort_order ";
                            break;
                        case 'name':
                            $sql .= " ovd.name ";
                            break;

                        default:
                            $sql .= " ov.option_value_id ";
                            break;
                    }

                    switch ($sort['type']) {
                        case 'asc':
                            $sql .= " ASC ";
                            break;

                        default:
                            $sql .= " DESC ";
                            break;
                    }

                    if ($i !== ($sortCount - 1)) {
                        $sql .= " , ";
                    }

                }
            }

        } else {
            $sql .= " ORDER BY ov.option_value_id DESC ";
        }

        if (isset($data['start']) || isset($data['limit'])) {

            if ($data['limit'] != -1) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = (int) $this->config->get('config_limit_admin');
                }

                $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
            }
        }

        return $this->db->query($sql)->rows;
    }

    public function prepareComplexCatalogOptionValues($data)
    {
        $this->load->model('tool/image');
        
        $optionId = $data['optionId'];

        return array_map(function($v) use ($optionId) {

            if (is_file(DIR_IMAGE . $v['image'])) {
                $imagePath = $v['image'];
                $imageThumb = $v['image'];
            } else {
                $imagePath = '';
                $imageThumb = 'no_image.png';
            }
            
            $names = array();
            foreach ($this->getLanguages() as $language) {
                $names[$language['languageId']] = array(
                    'languageId' => $language['languageId'],
                    'languageCode' => $language['code'],
                    'languageImage' => "language/{$language['code']}/{$language['code']}.png",
                    'languageName' => $language['name'],
    
                    'content' => html_entity_decode($this->getCatalogOptionValueName(
                        $optionId, $v['option_value_id'], $language['languageId'])),
                );
            }

            return array(
                'optionValueId' => (int) $v['option_value_id'],
                'name' => $names,
                'imagePath' => (string) $v['image'],
                'imageThumb' => (string) $this->model_tool_image->resize($imageThumb, 50, 50),
                'sortOrder' => (int) $v['sort_order'],
            );
        }, $this->getComplexCatalogOptionValues($data));
    }

    private function getCatalogOptionValueName($optionId, $optionValueId, $languageId)
    {   
        $q = $this->db->query("SELECT `name`
            FROM `". DB_PREFIX ."option_value_description`
            WHERE `option_id` = '". (int) $optionId ."'
            AND `option_value_id` = '". (int) $optionValueId ."'
            AND `language_id` = '". (int) $languageId ."'");

        if (isset($q->row['name'])) {
            return $q->row['name'];
        }
        return '';
    }

    private function isCatalogOptionValueName($optionId, $optionValueId, $languageId)
    {
        $nameData = $this->db->query("SELECT `name`
            FROM `". DB_PREFIX ."option_value_description`
            WHERE `option_id` = '". (int) $optionId ."'
            AND `option_value_id` = '". (int) $optionValueId ."'
            AND `language_id` = '". (int) $languageId ."'")->row;
        
        if (isset($nameData['name'])) {
            return true;
        }

        return false;
    }

    public function updateCatalogOptionValueField($optionId, $optionValueId, $name, $content)
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "option_value` 
            SET `". $this->db->escape($name) ."` = '" . $this->db->escape($content) . "'
            WHERE `option_id` = '" . (int) $optionId . "'
            AND `option_value_id` = '" . (int) $optionValueId . "'");
    }

    public function updateCatalogOptionValueName($optionId, $optionValueId, $languageId, $content)
    {
        if ($this->isCatalogOptionValueName($optionId, $optionValueId, $languageId)) {
            $this->db->query("UPDATE `" . DB_PREFIX . "option_value_description` 
                SET `name` = '" . $this->db->escape($content) . "'
                WHERE `option_id` = '" . (int) $optionId . "'
                AND `option_value_id` = '" . (int) $optionValueId . "'
                AND `language_id` = '". (int) $languageId ."'");
        } else {
            $this->db->query("INSERT INTO `". DB_PREFIX ."option_value_description`
                SET `option_id` = '" . (int) $optionId . "',
                    `option_value_id` = '" . (int) $optionValueId . "',
                    `language_id` = '" . (int) $languageId . "',
                    `name` = '" . $this->db->escape($content) . "'");
        }
    }

    public function deleteCatalogOptionValue($optionId, $optionValueId)
    {   
        $this->db->query("DELETE FROM `" . DB_PREFIX . "option_value`
            WHERE `option_id` = '" . (int) $optionId . "'
            AND `option_value_id` = '" . (int) $optionValueId . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "option_value_description`
            WHERE `option_id` = '" . (int) $optionId . "'
            AND `option_value_id` = '" . (int) $optionValueId . "'");
    }

    public function deleteCatalogOptionValuesForOptionId($optionId)
    {   
        $this->db->query("DELETE FROM `" . DB_PREFIX . "option_value`
            WHERE `option_id` = '" . (int) $optionId . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "option_value_description`
            WHERE `option_id` = '" . (int) $optionId . "'");
    }

    public function getDefaultImageThumb()
    {
        $this->load->model('tool/image');
        return $this->model_tool_image->resize('no_image.png', 50, 50);
    }

    public function getDefaultOptionValueName()
    {
        $names = array();
        foreach ($this->getLanguages() as $language) {
            $names[$language['languageId']] = array(
                'languageId' => $language['languageId'],
                'languageCode' => $language['code'],
                'languageImage' => "language/{$language['code']}/{$language['code']}.png",
                'languageName' => $language['name'],

                'content' => '',
            );
        }
        return $names;
    }

    public function saveCatalogOptionValue($optionId, $data)
    {
        $json['saved'] = false;

        foreach ($data['name'] as $nameData) {
            if (empty($nameData['content'])) {
                $json['error'][] = sprintf(
                    $this->language->get('error_catalog_option_name'), 
                    $nameData['languageName']);
            }
        }

        if (!isset($data['image'])) {
            $json['error'][] = $this->language->get('error_catalog_option_image');
        }

        if (!isset($data['sortOrder'])) {
            $json['error'][] = $this->language->get('error_catalog_option_sort_order');
        }

        if (!isset($json['error'])) {

            $data['optionValueId'] = $this->addCatalogOptionValue($optionId, $data);
            $this->editCatalogOptionValueName($optionId, $data['optionValueId'], $data['name']);

            $json['success'][] = sprintf(
                $this->language->get('success_catalog_option_value_created'), 
                $data['optionValueId']);
            
            $json['saved'] = true;
        }

        return $json;
    }

    private function addCatalogOptionValue($optionId, $data)
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "option_value` 
            SET `option_id` = '" . (int) $optionId . "',
                `image` = '". $this->db->escape($data['image']) ."',
                `sort_order` = '" . (int) $data['sortOrder'] . "'");
        
        return $this->db->getLastId();
    }

    public function editCatalogOptionValueName($optionId, $optionValueId, $names)
    {
        $this->db->query("DELETE FROM `". DB_PREFIX ."option_value_description`
            WHERE `option_id` = '" . (int) $optionId . "'
            AND `option_value_id` = '". (int) $optionValueId ."'");

        foreach ($names as $languageId => $value) {
            $this->db->query("INSERT INTO `". DB_PREFIX ."option_value_description`
                SET `option_id` = '" . (int) $optionId . "',
                    `option_value_id` = '". (int) $optionValueId ."',
                    `language_id` = '" . (int) $languageId . "',
                    `name` = '" . $this->db->escape($value['content']) . "'");
        }
    }
    /* OPTION VALUES END */

}