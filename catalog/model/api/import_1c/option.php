<?php
class ModelApiImport1COption extends Model
{
    private $codename = 'option';
    private $route = 'api/import_1c/option';

    const OPTION_TABLE = 'option';
    const OPTION_VALUE_TABLE = 'option_value';

    const O_SIZE = 'Размер';
    const O_COLOR = 'Цвет';

    const TYPE = 'radio';

    function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('api/import_1c/helper');
    }

    private function getAllowedOptions()
    {
        return array(
            self::O_SIZE,
            self::O_COLOR,
        );
    }

    public function action($parsed, $languages)
    {
        if (isset($parsed->classificator->options)
            && is_array($parsed->classificator->options)) {

            foreach ($parsed->classificator->options as $option) {
                if (in_array(trim($option->name), $this->getAllowedOptions())) {

                    $od = array();
                    foreach ($languages as $l) {
                        $od[$l] = array(
                            'name' => trim($option->name),
                        );
                    }

                    $d_ = array(
                        'type' => self::TYPE,
                        'import_id' => $option->id,
                        'sort_order' => 0,
                        'option_description' => $od,
                    );

                    if (!$this->model_api_import_1c_helper->isImportRecordExist(
                        self::OPTION_TABLE, $option->id)) {
                        $option_id = $this->addOption($d_);
                    } else {
                        $option_id = $this->getOptionByImportId($option->id);
                        $this->deleteOldValues($option_id);
                    }


                    if (isset($option->variants) && is_array($option->variants)) {
                        $option_values = array();
                        foreach ($option->variants as $k => $variant) {
                            $ovd = array();
                            foreach ($languages as $l) {
                                $ovd[$l] = array(
                                    'name' => trim($variant->value),
                                );
                            }

                            $option_values[] = array(
                                'image' => '',
                                'sort_order' => 0,
                                'import_id' => $variant->id,
                                'option_value_description' => $ovd,
                            );
                        }

                        $this->addOptionValues($option_id, $option_values);
                    }
                }
            }
        }
    }

    private function getOptionByImportId($import_id)
    {
        $query = $this->db->query("SELECT `option_id`
            FROM `". DB_PREFIX ."option`
            WHERE `import_id` = '".$this->db->escape($import_id)."'");
        if ($query->row) {
            return $query->row['option_id'];
        }
    }

    private function addOption($data) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "option`
            SET type = '" . $this->db->escape($data['type']) . "',
                sort_order = '" . (int)$data['sort_order'] . "',
                `import_id` = '" . $this->db->escape($data['import_id']) . "'");

        $option_id = $this->db->getLastId();

        foreach ($data['option_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "option_description
                SET option_id = '" . (int)$option_id . "',
                    language_id = '" . (int)$language_id . "',
                    name = '" . $this->db->escape($value['name']) . "'");
        }

        return $option_id;
    }

    private function addOptionValues($option_id, $option_values)
    {
        if (isset($option_values)) {
            foreach ($option_values as $option_value) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "option_value
                    SET option_id = '" . (int)$option_id . "',
                        image = '" . $this->db->escape(html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8')) . "',
                        sort_order = '" . (int)$option_value['sort_order'] . "',
                        `import_id` = '" . $this->db->escape($option_value['import_id']) . "'");

                $option_value_id = $this->db->getLastId();

                foreach ($option_value['option_value_description'] as $language_id => $option_value_description) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description
                        SET option_value_id = '" . (int)$option_value_id . "',
                            language_id = '" . (int)$language_id . "',
                            option_id = '" . (int)$option_id . "',
                            name = '" . $this->db->escape($option_value_description['name']) . "'");
                }
            }
        }
    }

    private function deleteOldValues($option_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "option_value WHERE option_id = '" . (int)$option_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "option_value_description WHERE option_id = '" . (int)$option_id . "'");
    }
}