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
        $this->load->model('api/import_1c/product');
        $this->load->model('api/import_1c/progress');
        $this->load->model('api/import_1c/option');
        $this->load->model('extension/module/super_offers');
    }

    public function action($parsed, $languages)
    {
        if (isset($parsed->offers_pack->offers)
            && is_array($parsed->offers_pack->offers)) {

            $prepared = array();

            foreach ($parsed->offers_pack->offers as $offer) {

                $json = array();
                $save = array(
                    'options' => array(),
                    'options_full' => array(),
                    'combinations' => array(),
                );

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

                if (isset($prepared[$product_id])) {
                    $save = $prepared[$product_id];
                }

                // PREPARE OPTIONS FOR COMBINATION
                $product_options = array();

                foreach ($offer->options as $option) {
                    $option_data = $this->prepareOptionData($parsed, $option);
                    if (isset($option_data['import_id']) && $option_data['import_id'] !== null) {

                        $ov = $this->model_api_import_1c_option->getOptionValueByImportId(
                            $option_data['import_id']);

                        if ($ov && $ov['option_id'] && $ov['option_value_id']) {

                            if (!in_array($ov['option_id'], $save['options'])) {
                                $save['options'][] = $ov['option_id'];
                            }

                            $check = false;
                            foreach ($save['options_full'] as $o) {
                                if ($o['option_id'] == $ov['option_id']
                                && $o['option_value_id'] == $ov['option_value_id']) {
                                    $check = true;
                                }
                            }

                            if ($check === false) {
                                $save['options_full'][] = $ov;
                            }


                            $product_options[] = array(
                                'option_id' => $ov['option_id'],
                                'option_value_id' => $ov['option_value_id'],
                            );
                        }
                    }
                }

                // SAVE COMBINATION
                $combination = array();

                $combinationImage = '';
                foreach ($this->model_extension_module_super_offers
                    ->_getCombinationsForProduct($product_id) as $_combination) {
                    if (isset($_combination['import_id']) && isset($_combination['image'])
                    && strcmp($_combination['import_id'], $offer_import_id) === 0) {
                        $combinationImage = $_combination['image'];
                    }
                }

                if ($product_options) {
                    $combination = array(
                        'quantity' => $offer->quantity,
                        'subtract' => true,
                        'price' => $offer->price->price,
                        'currency' => $offer->price->currency,
                        'product_code' => $offer->name,
                        'name_for_print' => $offer->nameForPrint,
                        'image' => $combinationImage,
                        'barcode' => $offer->barcode,
                        'import_id' => $offer_import_id,
                    );

                    foreach ($product_options as $k => $v) {
                        $combination["{$k}__{$v['option_id']}"] = $v['option_value_id'];
                    }
                }

                if ($combination) {
                    $save['combinations'][] = $combination;
                }

                $prepared[$product_id] = $save;

                // SAVE TO LOG
                $this->model_api_import_1c_progress->parseJson($json);
            }

            // USE PREPARED DATA
            foreach ($prepared as $product_id => $data) {

                // CLEAR OLD COMBINATIONS
                $this->model_extension_module_super_offers->clearForProduct($product_id);

                foreach ($data['options'] as $option_id) {

                    // ASSIGN OPTIONS TO PRODUCT
                    $po = $this->getProductOption($product_id, $option_id);

                    if (!$po) {
                        $po = $this->addProductOption($product_id, array(
                            'option_id' => $option_id,
                            'required' => true,
                        ));
                    }

                    foreach ($data['options_full'] as $of) {
                        if ($of['option_id'] == $option_id) {

                            if (!$this->isProductOptionValue($product_id, array(
                                'option_id' => $option_id,
                                'product_option_id' => $po,
                                'option_value_id' => $of['option_value_id'],
                            ))) {

                                $pov = $this->addProductOptionValue($product_id, array(
                                    'option_id' => $option_id,
                                    'product_option_id' => $po,
                                    'option_value_id' => $of['option_value_id'],
                                    'quantity' => 0,
                                    'subtract' => true,
                                    'price' => 0,
                                    'price_prefix' => '+',
                                    'points' => 0,
                                    'points_prefix' => '+',
                                    'weight' => 0,
                                    'weight_prefix' => '+',
                                ));
                                unset($pov);
                            }

                        }
                    }

                    unset($po);
                }

                // CLEAR UNUSED OPTIONS
                foreach ($this->getProductOptions($product_id) as $po) {
                    if (!in_array($po['option_id'], $data['options'])) {
                        $this->deleteProductOption($product_id, $po['product_option_id']);
                    }
                }

                foreach ($this->getProductOptionValues($product_id) as $pov) {
                    $check = false;
                    foreach ($data['options_full'] as $of) {
                        if ($pov['option_value_id'] == $of['option_value_id']
                        && $pov['option_id'] == $of['option_id']) {
                            $check = true;
                        }
                    }

                    if ($check === false) {
                        $this->deleteProductOptionValue($pov['product_option_value_id']);
                    }
                }


                // ADD COMBINATIONS
                if ($data['combinations']) {
                    $this->model_extension_module_super_offers->saveCombinations(
                        $product_id, $data['combinations']);
                }

                // UPDATE PRODUCT STATUS
                $available = false;
                if ($data['combinations']) {
                    foreach ($data['combinations'] as $c) {
                        if (isset($c['price']) && isset($c['quantity'])
                        && $c['quantity'] > 0 && $c['price'] > 0) {
                            $available = true;
                        }
                    }
                }
                $this->model_api_import_1c_product->updateProductStatus(
                    $product_id, $available);

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

    private function isProductOption($product_id, $option_id)
    {
        if ($this->getProductOption($product_id, $option_id)) {
            return true;
        }
        return false;
    }

    private function getProductOption($product_id, $option_id)
    {
        $query = $this->db->query("SELECT `product_option_id`
            FROM `". DB_PREFIX ."product_option`
            WHERE `product_id` = '".$this->db->escape($product_id)."'
            AND `option_id` = '".$this->db->escape($option_id)."'");
        if ($query->row) {
            return $query->row['product_option_id'];
        }
    }

    private function getProductOptions($product_id)
    {
        $query = $this->db->query("SELECT `product_option_id`, `option_id`
            FROM `". DB_PREFIX ."product_option`
            WHERE `product_id` = '".$this->db->escape($product_id)."'");
        if ($query->rows) {
            return $query->rows;
        }
    }

    private function addProductOption($product_id, $product_option)
    {
        $this->db->query("INSERT INTO ". DB_PREFIX ."product_option
            SET product_id = '" . (int)$product_id . "',
                option_id = '" . (int)$product_option['option_id'] . "',
                required = '" . (int)$product_option['required'] . "'");

        return $this->db->getLastId();
    }

    private function isProductOptionValue($product_id, $data)
    {
        $query = $this->db->query("SELECT `product_option_id`
            FROM `". DB_PREFIX ."product_option_value`
            WHERE `product_id` = '".$this->db->escape($product_id)."'
            AND `option_id` = '".$this->db->escape($data['option_id'])."'
            AND `product_option_id` = '".$this->db->escape($data['product_option_id'])."'
            AND `option_value_id` = '".$this->db->escape($data['option_value_id'])."'");
        if ($query->row) {
            return true;
        } else {
            return false;
        }
    }

    private function addProductOptionValue($product_id, $data)
    {
        $this->db->query("INSERT INTO ". DB_PREFIX ."product_option_value
            SET product_option_id = '" . (int)$data['product_option_id'] . "',
                product_id = '" . (int)$product_id . "',
                option_id = '" . (int)$data['option_id'] . "',
                option_value_id = '" . (int)$data['option_value_id'] . "',
                quantity = '" . (int)$data['quantity'] . "',
                subtract = '" . (int)$data['subtract'] . "',
                price = '" . (float)$data['price'] . "',
                price_prefix = '" . $this->db->escape($data['price_prefix']) . "',
                points = '" . (int)$data['points'] . "',
                points_prefix = '" . $this->db->escape($data['points_prefix']) . "',
                weight = '" . (float)$data['weight'] . "',
                weight_prefix = '" . $this->db->escape($data['weight_prefix']) . "'");

        return $this->db->getLastId();
    }

    private function getProductOptionValues($product_id)
    {
        $query = $this->db->query("SELECT *
            FROM `". DB_PREFIX ."product_option_value`
            WHERE `product_id` = '".$this->db->escape($product_id)."'");
        if ($query->rows) {
            return $query->rows;
        }
    }

    public function deleteProductOptionValue($product_option_value_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value
            WHERE product_option_value_id = '" . (int)$product_option_value_id . "'");
    }

    public function deleteProductOption($product_id, $product_option_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option
            WHERE product_id = '" . (int)$product_id . "'
            AND product_option_id = '" . (int)$product_option_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value
            WHERE product_id = '" . (int)$product_id . "'
            AND product_option_id = '" . (int)$product_option_id . "'");
    }

    public function deleteProductOptions($product_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option
            WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value
            WHERE product_id = '" . (int)$product_id . "'");
    }
}