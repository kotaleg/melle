<?php

class ModelExtensionModuledblogmodulepopularposts extends Model {

    public function getPopularPost($limit,$blog_category)
    {
        $sql = "SELECT p.post_id ";


        if (!empty($blog_category)) {
            $categories = join("','",$blog_category);
            $sql .= " FROM " . DB_PREFIX . "bm_post_to_category p2c
            LEFT JOIN " . DB_PREFIX . "bm_post p ON(p2c.post_id = p.post_id AND p2c.category_id IN ('".$categories."'))";
        } else {
            $sql .= " FROM " . DB_PREFIX . "bm_post p ";
        }

        $sql .= " LEFT JOIN " . DB_PREFIX . "bm_post_description pd ON (p.post_id = pd.post_id) "
        . "LEFT JOIN " . DB_PREFIX . "bm_post_to_store p2s ON (p.post_id = p2s.post_id) "
        . "Left JOIN " . DB_PREFIX . "bm_review r ON (p.post_id = r.post_id) "
        . "WHERE pd.language_id = '" . (int) $this->config->get('config_language_id') . "' "
        . "AND p2s.store_id = '".(int) $this->config->get('config_store_id')."' "
        . "AND p.status = '1' ". " AND p.date_published < NOW()";
        $sql .= " GROUP BY p.post_id";
        $sql .=" ORDER BY p.viewed DESC";
        $sql .= " LIMIT 0, ".$limit;
        $query = $this->db->query($sql);

        return $query->rows;
    }

}
