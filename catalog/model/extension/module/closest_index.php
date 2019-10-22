<?php
/*
 *  location: catalog/model
 */
class ModelExtensionModuleClosestIndex extends Model
{
    const INDEX_TABLE = 'closest_index';

    public function __construct($registry)
    {
        parent::__construct($registry);
    }

    public function injectSql($data)
    {
        $currentCategory = 0;
        if (isset($data['filter_category_id'])) {
            $currentCategory = $data['filter_category_id'];
        }

        if ($currentCategory == 0) {
            return ", NULL AS closestSortOrder";
        }

        return ",
            (SELECT ci.sortOrder FROM `". DB_PREFIX . self::INDEX_TABLE ."` ci
            WHERE ci.productId = p.product_id
            AND

                (SELECT COUNT(p2cccc.category_id) FROM " . DB_PREFIX . "product_to_category p2cccc
                WHERE p2cccc.product_id = p.product_id AND p2cccc.category_id = '". (int)$currentCategory ."' LIMIT 1) > 0

            LIMIT 1)

            AS closestSortOrder,

            (SELECT COUNT(p2cccc.category_id) FROM " . DB_PREFIX . "product_to_category p2cccc
                WHERE p2cccc.product_id = p.product_id AND p2cccc.category_id = '". (int)$currentCategory ."' LIMIT 1) AS yooo";
    }

}