<?php
class ModelExtensionModuleDBlogModuleDate extends Model {

    public function getDates()
    {
        $query = $this->db->query("SELECT Year(`date_published`) as year, Month(`date_published`) as month, count(*) as total FROM `".DB_PREFIX."bm_post` GROUP BY Year(`date_published`), Month(`date_published`) ");
        return $query->rows;
    }

}