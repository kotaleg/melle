<?php

class ModelExtensionModuledblogmodulerelatedproduct extends Model {

    public function getPostProducts($post_id, $limit = 3)
    {
        $query = $this->db->query("SELECT p2p.product_id AS product_id, pd.name AS product_title
            FROM " . DB_PREFIX . "bm_post_to_product p2p
            LEFT JOIN " . DB_PREFIX . "product_description pd ON (p2p.product_id = pd.product_id)
            WHERE p2p.post_id = '" . (int) $post_id . "' AND pd.language_id ='" . (int)$this->config->get('config_language_id') . "' LIMIT " . $limit  );

        return $query->rows;
    }

}