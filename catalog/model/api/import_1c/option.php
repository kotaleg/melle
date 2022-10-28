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
        $this->load->model('api/import_1c/progress');
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

            $find_image = 0;
            $imported = array();

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

                    $old_values = array();
                    if (!$this->model_api_import_1c_helper->isImportRecordExist(
                        self::OPTION_TABLE, $option->id)) {
                        $option_id = $this->addOption($d_);
                    } else {
                        $option_id = $this->getOptionByImportId($option->id);
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

                            $image = '';

                            $img = $this->getColorByImportID($variant->id);
                            if ($img) { $image = $img; }

                            if (empty($image)) { $find_image++; }

                            $option_values[] = array(
                                'image' => $image,
                                'sort_order' => 0,
                                'import_id' => $variant->id,
                                'option_value_description' => $ovd,
                            );
                        }

                        foreach ($option_values as $value) {
                            $ov = $this->getOptionValueByImportId($value['import_id']);
                            if ($ov && isset($ov['option_value_id'])) {
                                $this->updateOptionValue($option_id, $ov['option_value_id'], $value);
                            } else {
                                $this->addOptionValues($option_id, array($value));
                            }
                        }

                    }
                }
            }

            $json['message'] = array();
            $json['message'][] = "Восстановлено изображений опций {$find_image}";

            // SAVE TO LOG
            $this->model_api_import_1c_progress->parseJson($json);
        }
    }

    public function getOptionByImportId($import_id)
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

    public function getOptionValueByImportId($import_id)
    {
        $query = $this->db->query("SELECT `option_value_id`, `option_id`
            FROM `". DB_PREFIX ."option_value`
            WHERE `import_id` = '".$this->db->escape($import_id)."'");
        if ($query->row) {
            return $query->row;
        }
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

    private function updateOptionValue($option_id, $option_value_id, $data)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "option_value
            SET image = '" . $this->db->escape($data['image']) . "',
                sort_order = '" . (int)$data['sort_order'] . "'
            WHERE option_value_id = '" . (int)$option_value_id . "'");

        if (isset($data['option_value_description']) && $data['option_value_description']) {
            $this->setOptionValueDescription($option_id, $option_value_id, $data['option_value_description']);
        }
    }

    private function setOptionValueDescription($option_id, $option_value_id, $data)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "option_value_description
            WHERE option_value_id = '" . (int)$option_value_id . "'");

        foreach ($data as $language_id => $option_value_description) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description
                SET option_value_id = '" . (int)$option_value_id . "',
                    language_id = '" . (int)$language_id . "',
                    option_id = '" . (int)$option_id . "',
                    name = '" . $this->db->escape($option_value_description['name']) . "'");
        }
    }

    public function getOptionValues($option_id)
    {
        $option_value_data = array();

        $option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value ov
            WHERE ov.option_id = '" . (int)$option_id . "'
            ORDER BY ov.sort_order");

        foreach ($option_value_query->rows as $option_value) {
            $option_value_data[$option_value['import_id']] = array(
                'option_value_id' => $option_value['option_value_id'],
                'image'           => $option_value['image'],
                'sort_order'      => $option_value['sort_order'],
                'import_id'       => $option_value['import_id'],
            );
        }

        return $option_value_data;
    }

    private function deleteOldValues($option_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "option_value
            WHERE option_id = '" . (int)$option_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "option_value_description
            WHERE option_id = '" . (int)$option_id . "'");
    }

    public function importColorCubs()
    {
        $q = $this->db->query("SELECT f.name, ci.image, f.c_id FROM tbl_color_items ci
            LEFT JOIN tbl_filters f ON (ci.filter_id = f.id)");

        $i = 0;
        foreach ($q->rows as $ci) {

            $image = 'catalog/colors/'.basename($ci['image']);

            $this->saveColorImage($ci['c_id'], $image);

            $o = $this->getOptionValueByImportId($ci['c_id']);
            if ($o) {
                $i++;
                $this->updateOptionImage($o['option_value_id'], $image);
            }
        }
    }

    private function getOptionByName($name)
    {
        $query = $this->db->query("SELECT ovd.option_value_id, ov.image
            FROM `". DB_PREFIX ."option_value_description` ovd
            LEFT JOIN `" . DB_PREFIX . "option_value` ov
            ON (ov.option_value_id = ovd.option_value_id)
            WHERE ovd.name LIKE '%".$this->db->escape($name)."%'");
        if ($query->row) {
            return $query->row;
        }
    }

    private function updateOptionImage($option_value_id, $image)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "option_value
            SET image = '" . $this->db->escape($image) . "'
            WHERE option_value_id = '" . (int)$option_value_id . "'");
    }

    public function saveColorImage($import_id, $image_path)
    {
        $this->load->model('extension/pro_patch/db');
        $sql = $this->model_extension_pro_patch_db->sqlOnDuplicateUpdateBuilder(
                'color_images',
                array(
                    'import_id' => array(
                        'update'    => false,
                        'data'      => $import_id,
                    ),
                    'image' => $image_path,
                ));

        $this->db->query($sql);
    }

    public function getColorByImportID($import_id)
    {
        $color_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "color_images`
            WHERE `import_id` = '".$this->db->escape($import_id)."'");
        if (isset($color_query->row['image']) && !empty($color_query->row['image'])) {
            return $color_query->row['image'];
        }
    }
}
