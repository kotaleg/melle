<?php

class ModelExtensionModuledblogmodulerelatedpost extends Model {

    public function getPostRelateds($post_id)
    {
        $query = $this->db->query("SELECT pr.post_related_id AS post_id, pd.title AS title
            FROM " . DB_PREFIX . "bm_post_related pr
            LEFT JOIN " . DB_PREFIX . "bm_post_description pd ON (pr.post_related_id = pd.post_id)
            WHERE pr.post_id = '" . (int) $post_id . "' AND pd.language_id ='" . (int)$this->config->get('config_language_id') . "'");

        $post_related_data = $query->rows;
        return $post_related_data;
    }

}