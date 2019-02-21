<?php
class ModelApiImport1CDiscount extends Model
{
    private $codename = 'product';
    private $route = 'api/import_1c/dicount';

    const PRODUCT_TABLE = 'product';

    const DISCOUNT_ZNACHEK = 'act';

    function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('api/import_1c/helper');
        $this->load->model('api/import_1c/progress');
        $this->load->model('api/import_1c/product');
        $this->load->model('extension/module/pro_znachek');
    }

    public function action($parsed)
    {
        $json = array();
        if (!$this->model_extension_module_pro_znachek->isZnachek(self::DISCOUNT_ZNACHEK)) {
            $json['error'][] = 'Тип значка `'.self::DISCOUNT_ZNACHEK.'` не существует';
        } else {
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
                    $info = $this->model_catalog_product->getProduct($product_id);
                    if ((float)$info['special'] > 0) {
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

        }

        // SAVE TO LOG
        $this->model_api_import_1c_progress->parseJson($json);
    }

    public function getDiscountsCount()
    {
        $q = $this->db->query("SELECT COUNT(`product_id`) AS total
            FROM`". DB_PREFIX ."product`
            WHERE `znachek` = '".self::DISCOUNT_ZNACHEK."'");

        if (isset($q->row['total'])) {
            return (int)$q->row['total'];
        } else { return 0; }
    }

    public function clearAllDiscounts()
    {
        $this->db->query("UPDATE `". DB_PREFIX ."product`
            SET `znachek` = ''
            WHERE `znachek` = '".self::DISCOUNT_ZNACHEK."'");
    }

    public function setDiscountZnachek($product_id)
    {
        $this->db->query("UPDATE `". DB_PREFIX ."product`
            SET `znachek` = '".self::DISCOUNT_ZNACHEK."'
            WHERE `product_id` = '". (int)$product_id ."'");
    }

}