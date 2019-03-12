<?php

class ModelExtensionModuledblogmodulerelatposttoprod extends Model {

    public function getPostRelateds($product_id)
    {
        $query = $this->db->query("SELECT pr.post_id AS post_id, pd.title AS title
            FROM " . DB_PREFIX . "bm_post_to_product pr
            LEFT JOIN " . DB_PREFIX . "bm_post_description pd ON (pr.post_id = pd.post_id)
            WHERE pr.product_id = '" . (int) $product_id . "' AND pd.language_id = '" . (int) $this->config->get('config_language_id') . "'");

        $post_related_data = $query->rows;
        return $post_related_data;
    }

}