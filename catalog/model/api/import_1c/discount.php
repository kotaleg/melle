<?php
class ModelApiImport1CDiscount extends Model
{
    private $codename = 'product';
    private $route = 'api/import_1c/dicount';

    const PRODUCT_TABLE = 'product';

    function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('api/import_1c/helper');
        $this->load->model('api/import_1c/progress');
        $this->load->model('api/import_1c/product');

        /* PRO DISCOUNT */
        if ((in_array(__FUNCTION__, array('__construct')))
        && file_exists(DIR_SYSTEM.'library/pro_hamster/extension/pro_discount.json')) {
            $this->load->model('extension/total/pro_discount');
        }
        /* PRO DISCOUNT */
    }

    public function action($parsed)
    {
        $json = array();

        $total_before = $this->getDiscountsCount();
        $this->clearAllDiscounts();

        if ($total_before > 0) {
            $json['message'][] = "{$total_before} акционных значков очищено";
        }

        try {

            $this->load->model('catalog/product');

            $count = 0;
            $products = $this->model_api_import_1c_product->getAllProductsIds();

            foreach ($products as $product_id) {

                $check = false;

                $info = $this->model_catalog_product->getProduct($product_id);
                if ((float)$info['special'] > 0) {
                    $check = true;
                }

                /* PRO DISCOUNT */
                if ((in_array(__FUNCTION__, array('action')))
                && file_exists(DIR_SYSTEM.'library/pro_hamster/extension/pro_discount.json')) {
                    $s = $this->model_extension_total_pro_discount->getSpecialPrice($product_id, 0);
                    $t = $this->model_extension_total_pro_discount->getSpecialText($product_id);

                    if ($s || $t !== false) {
                        $check = true;
                    }
                }
                /* PRO DISCOUNT */

                if ($check === true) {
                    $this->setDiscountZnachek($product_id);
                    $count++;
                }
            }

            if ($count > 0) {
                $json['message'][] = "Присвоено {$count} акционных значков";
            }

        } catch (Exception $e) {
            $json['error'][] = 'Не удалось применить акционные значки';
        }

        // SAVE TO LOG
        $this->model_api_import_1c_progress->parseJson($json);
    }

    public function getDiscountsCount()
    {
        $q = $this->db->query("SELECT COUNT(`product_id`) AS total
            FROM`". DB_PREFIX ."product`
            WHERE `shitty_discount` = '". (int)true ."'");

        if (isset($q->row['total'])) {
            return (int)$q->row['total'];
        } else { return 0; }
    }

    public function clearAllDiscounts()
    {
        $this->db->query("UPDATE `". DB_PREFIX ."product`
            SET `shitty_discount` = ''
            WHERE `shitty_discount` = '". (int)true ."'");
    }

    public function setDiscountZnachek($product_id)
    {
        $this->db->query("UPDATE `". DB_PREFIX ."product`
            SET `shitty_discount` = '". (int)true ."'
            WHERE `product_id` = '". (int)$product_id ."'");
    }

}