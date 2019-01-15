<?php
class ModelApiImport1COffer extends Model
{
    private $codename = 'offer';
    private $route = 'api/import_1c/offer';

    const PRODUCT_TABLE = 'product';

    const SPRAVOSHNIK = 'Справочник';

    function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('api/import_1c/helper');
        $this->load->model('extension/module/super_offers');
        $this->load->model('api/import_1c/product');
    }

    public function action($parsed, $languages)
    {
        if (isset($parsed->offers_pack->offers)
            && is_array($parsed->offers_pack->offers)) {

            foreach ($parsed->offers_pack->offers as $offer) {

                $json = array();

                $ex = explode('#', $offer->id);
                if (!isset($ex[0]) && !isset($ex[1])) {
                    $json['error'][] = "Не удалось разбить offer ID - {$offer->id}";

                    // SAVE TO LOG
                    $this->model_api_import_1c_progress->parseJson($json);
                    continue;
                }
                $product_import_id = $ex[0];
                $offer_import_id = $ex[1];

                $product_id = $this->model_api_import_1c_product->getProductByImportId($product_import_id);
                if (!$product_id) {
                    $json['error'][] = "Не удалось найти продукт. IMPORT_ID = {$product_import_id}";

                    // SAVE TO LOG
                    $this->model_api_import_1c_progress->parseJson($json);
                    continue;
                }

                // CLEAR OLD OPTIONS
                $this->model_api_import_1c_product->deleteProductOptions($product_id);
                $this->model_extension_module_super_offers->clearForProduct($product_id);

                // SUPER OFFERS DATA
                $so_data = array(
                    'name' => $offer->name,
                    'quantity' => $offer->quantity,
                    'price' => $offer->price->price,
                    'currency' => $offer->price->currency,
                );

                $product_options = array();

                foreach ($offer->options as $option) {
                    $option_data = $this->prepareOptionData($parsed, $option);
                    if ($option_data['value'] !== null) {
                        // $po = $this->addProductOption($product_id, array(
                        //     'option_id' => ,
                        //     'required' => true,
                        // ));
                    }

                    // echo "<pre>"; print_r($option_data); echo "</pre>";exit;
                }

                // echo "<pre>"; print_r($offer); echo "</pre>";exit;


                // SAVE TO LOG
                $this->model_api_import_1c_progress->parseJson($json);
            }

        }
    }

    private function prepareOptionData($parsed, $offer_option)
    {
        $result = array(
            'name' => null,
            'value' => null,
        );

        foreach ($parsed->classificator->options as $option) {
            if (strcmp((string)$option->id, (string)$offer_option->id) === 0) {
                $result['name'] = trim($offer_option->name);

                if (strcmp((string)trim($option->type), self::SPRAVOSHNIK) === 0) {
                    foreach ($option->variants as $variant) {
                        if (strcmp((string)$variant->id, (string)$offer_option->value) === 0) {
                            $result['import_id'] = trim((string)$variant->id);
                            $result['value'] = trim((string)$variant->value);
                        }
                    }
                } else {
                    $result['value'] = trim($offer_option->value);
                }
            }
        }

        return $result;
    }

    private function addProductOption($product_id, $product_option)
    {
        $this->db->query("INSERT INTO ". DB_PREFIX ."product_option
            SET product_id = '" . (int)$product_id . "',
                option_id = '" . (int)$product_option['option_id'] . "',
                required = '" . (int)$product_option['required'] . "'");

        $product_option_id = $this->db->getLastId();
    }

    private function addProductOptionvalues($product_id, $product_option_id, $product_option_values)
    {
        foreach ($product_option_values as $product_option_value) {
            $this->db->query("INSERT INTO ". DB_PREFIX ."product_option_value
                SET product_option_value_id = '" . (int)$product_option_value['product_option_value_id'] . "',
                    product_option_id = '" . (int)$product_option_id . "',
                    product_id = '" . (int)$product_id . "',
                    option_id = '" . (int)$product_option['option_id'] . "',
                    option_value_id = '" . (int)$product_option_value['option_value_id'] . "',
                    quantity = '" . (int)$product_option_value['quantity'] . "',
                    subtract = '" . (int)$product_option_value['subtract'] . "',
                    price = '" . (float)$product_option_value['price'] . "',
                    price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "',
                    points = '" . (int)$product_option_value['points'] . "',
                    points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "',
                    weight = '" . (float)$product_option_value['weight'] . "',
                    weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
        }
    }
}