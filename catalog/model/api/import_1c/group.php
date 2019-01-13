<?php
class ModelApiImport1CGroup extends Model
{
    private $codename = 'group';
    private $route = 'api/import_1c/group';

    const ATTRIBUTE_TABLE = 'attribute';
    const AG_ATTRIBURES = 'Атрибуты';
    const A_GROUP = 'Группа';

    function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('api/import_1c/helper');
    }

    public function action($name, $languages)
    {
        $attribute_group = $this->getAttributeGroupByName(self::AG_ATTRIBURES);
        if (!$attribute_group) {
            $agd = array();
            foreach ($languages as $l) {
                $agd[$l] = array(
                    'name'  => self::AG_ATTRIBURES,
                );
            }

            $attribute_group = $this->addAttributeGroup(array(
                'sort_order'                    => 0,
                'attribute_group_description'   => $agd,
            ));
        }

        $attribute = $this->getAttributeByName($name);
        if (!$attribute) {
            $ad = array();
            foreach ($languages as $l) {
                $ad[$l] = array(
                    'name'  => $name,
                );
            }

            $this->addAttribute(array(
                'attribute_group_id' => $attribute_group,
                'sort_order' => 0,
                'attribute_description' => $ad,
            ));
        }
    }

    public function getAttributeGroupByName($name)
    {
        $query = $this->db->query("SELECT `attribute_group_id`
            FROM `". DB_PREFIX ."attribute_group_description`
            WHERE `name` LIKE '".$this->db->escape(trim($name))."'");
        if ($query->num_rows) {
            return $query->row['attribute_group_id'];
        }
    }

    public function addAttributeGroup($data)
    {
        $this->db->query("INSERT INTO ". DB_PREFIX ."attribute_group
            SET sort_order = '" . (int)$data['sort_order'] . "'");

        $attribute_group_id = $this->db->getLastId();

        foreach ($data['attribute_group_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_group_description
                SET attribute_group_id = '" . (int)$attribute_group_id . "',
                    language_id = '" . (int)$language_id . "',
                    name = '" . $this->db->escape($value['name']) . "'");
        }

        return $attribute_group_id;
    }

    public function getAttributeByName($name)
    {
        $query = $this->db->query("SELECT `attribute_id`
            FROM `". DB_PREFIX ."attribute_description`
            WHERE `name` LIKE '".$this->db->escape(trim($name))."'");
        if ($query->num_rows) {
            return $query->row['attribute_id'];
        }
    }

    public function addAttribute($data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "attribute
            SET attribute_group_id = '" . (int)$data['attribute_group_id'] . "',
                sort_order = '" . (int)$data['sort_order'] . "'");

        $attribute_id = $this->db->getLastId();

        foreach ($data['attribute_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "attribute_description
                SET attribute_id = '" . (int)$attribute_id . "',
                    language_id = '" . (int)$language_id . "',
                    name = '" . $this->db->escape($value['name']) . "'");
        }

        return $attribute_id;
    }

    public function prepareProductAttribute($name, $value, $languages)
    {
        $attribute_id = $this->model_api_import_1c_group->getAttributeByName($name);
        if ($attribute_id) {
            $pad = array();
            foreach ($languages as $l) {
                $pad[$l] = array(
                    'text' => $value,
                );
            }

            return array(
                'attribute_id' => $attribute_id,
                'product_attribute_description' => $pad,
            );
        }

        return null;
    }
}