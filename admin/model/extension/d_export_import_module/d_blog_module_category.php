<?php
class ModelExtensionDExportImportModuleDBlogModuleCategory extends Controller {

    private $codename = 'd_export_import';

    public function repairCategories($parent_id = 0){
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "bm_category WHERE parent_id = '" . (int)$parent_id . "'");

        foreach ($query->rows as $category) {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "bm_category_path` WHERE category_id = '" . (int)$category['category_id'] . "'");

            $level = 0;

            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "bm_category_path` WHERE category_id = '" . (int)$parent_id . "' ORDER BY level ASC");

            foreach ($query->rows as $result) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "bm_category_path` SET category_id = '" . (int)$category['category_id'] . "', `path_id` = '" . (int)$result['path_id'] . "', level = '" . (int)$level . "'");

                $level++;
            }

            $this->db->query("REPLACE INTO `" . DB_PREFIX . "bm_category_path` SET category_id = '" . (int)$category['category_id'] . "', `path_id` = '" . (int)$category['category_id'] . "', level = '" . (int)$level . "'");

            $this->repairCategories($category['category_id']);
        }
    }
}