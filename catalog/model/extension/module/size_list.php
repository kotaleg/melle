<?php
/*
 *  location: catalog/model
 */
class ModelExtensionModuleSizeList extends Model
{
    private $codename = 'size_list';
    private $route = 'extension/module/size_list';

    const IMAGE_TABLE = 'sl_images';
    const PRODUCT_TABLE = 'sl_product';

    public function getSizeList($product_id)
    {
        $image = false;

        $q = $this->db->query("SELECT i.image
            FROM `". DB_PREFIX . self::IMAGE_TABLE ."` i
            LEFT JOIN `". DB_PREFIX . self::PRODUCT_TABLE ."` p
            ON(i.image_id = p.image_id)
            WHERE p.product_id = '". (int)$product_id ."'
            LIMIT 1");

        if ($q->row && isset($q->row['image'])) {
            $this->load->model('tool/image');
            $img = $this->model_tool_image->resize($q->row['image'], 600, 400);
            if ($img) { $image = $img; }
        }

        return $image;
    }
}