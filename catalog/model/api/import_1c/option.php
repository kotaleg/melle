<?php
class ModelApiImport1COption extends Model
{
    private $codename = 'option';
    private $route = 'api/import_1c/option';

    const CATEGORY = 'Категория';
    const COLLECTION = 'Коллекция';
    const MATERIAL = 'Материал';

    const CATEGORY_TABLE = 'category';

    const IMPORT_FIELD = 'import_id';

    function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('api/import_1c/helper');
        $this->load->model('api/import_1c/group');
    }

    public function action($parsed, $languages)
    {
        if (isset($parsed->classificator->options)
            && is_array($parsed->classificator->options)) {

            foreach ($parsed->classificator->options as $option) {
                switch (trim($option->name)) {
                    case self::CATEGORY:
                        foreach ($option->variants as $k => $item) {

                            $cd = array();
                            foreach ($languages as $l) {
                                $cd[$l] = array(
                                    'name'  => trim($item->value),
                                    'description' => '',
                                    'meta_title' => trim($item->value),
                                    'meta_description' => '',
                                    'meta_keyword' => '',
                                );
                            }

                            $d_ = array(
                                'import_id' => $item->id,
                                'parent_id' => 0,
                                'column' => 1,
                                'sort_order' => $k,
                                'status' => 1,
                                'category_description' => $cd,
                                'category_store' => array(
                                    0 => $this->config->get('config_store_id'),
                                ),
                            );

                            if (!$this->model_api_import_1c_helper->isImportRecordExist(
                                self::CATEGORY_TABLE, $item->id)) {
                                $this->addCategory($d_);
                            } else {
                                // TODO: edit category?
                                // $this->editCategory($item->id, $d_);
                            }
                        }
                        break;

                    case self::COLLECTION:
                        $this->model_api_import_1c_group->action(
                            trim($option->name), $languages);
                        break;

                    case self::MATERIAL:
                        $this->model_api_import_1c_group->action(
                            trim($option->name), $languages);
                        break;
                }
            }
        }
    }

    public function addCategory($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "category
            SET parent_id = '" . (int)$data['parent_id'] . "',
            `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "',
            `column` = '" . (int)$data['column'] . "',
            sort_order = '" . (int)$data['sort_order'] . "',
            status = '" . (int)$data['status'] . "',
            date_modified = NOW(),
            date_added = NOW(),
            `import_id` = '" . $this->db->escape($data['import_id']) . "'");

        $category_id = $this->db->getLastId();

        foreach ($data['category_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "category_description
                SET category_id = '" . (int)$category_id . "',
                    language_id = '" . (int)$language_id . "',
                    name = '" . $this->db->escape($value['name']) . "',
                    description = '" . $this->db->escape($value['description']) . "',
                    meta_title = '" . $this->db->escape($value['meta_title']) . "',
                    meta_description = '" . $this->db->escape($value['meta_description']) . "',
                    meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
        }

        // MySQL Hierarchical Data Closure Table Pattern
        $level = 0;

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path`
            WHERE category_id = '" . (int)$data['parent_id'] . "'
            ORDER BY `level` ASC");

        foreach ($query->rows as $result) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "category_path`
                SET `category_id` = '" . (int)$category_id . "',
                    `path_id` = '" . (int)$result['path_id'] . "',
                    `level` = '" . (int)$level . "'");
            $level++;
        }

        $this->db->query("INSERT INTO `" . DB_PREFIX . "category_path`
            SET `category_id` = '" . (int)$category_id . "',
                `path_id` = '" . (int)$category_id . "',
                `level` = '" . (int)$level . "'");

        if (isset($data['category_store'])) {
            foreach ($data['category_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "category_to_store
                    SET category_id = '" . (int)$category_id . "',
                        store_id = '" . (int)$store_id . "'");
            }
        }

        $this->cache->delete('category');
        return $category_id;
    }
}