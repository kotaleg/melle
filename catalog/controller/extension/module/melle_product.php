<?php
/*
 *  location: catalog/controller
 */
class ControllerExtensionModuleMelleProduct extends Controller
{
    private $codename = 'melle_product';
    private $route = 'extension/module/melle_product';
    private $type = 'module';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model($this->route);
        $this->load->model('extension/pro_patch/url');
        $this->load->model('extension/pro_patch/load');
        $this->load->model('extension/pro_patch/user');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/setting');
        $this->load->model('tool/base');

        $this->extension_model = $this->{'model_'.str_replace("/", "_", $this->route)};
    }

    public function getProductPreviewData()
    {
        $parsed = $this->model_extension_pro_patch_json
            ->parseJson(file_get_contents('php://input'));

        $json = array();
        $this->response->addHeader('Content-Type: application/json');

        if (!isset($parsed['productId'])) {
            $json['error'][] = 'Обязательный параметр productId отсутствует';
            return $this->response->setOutput(json_encode($json));
        }

        $this->load->language('product/product');
        $this->load->model('catalog/product');
        $this->load->model('extension/module/super_offers');
        $this->load->model('extension/total/pro_discount');
        $this->load->model('tool/image');

        $productId = (int) $parsed['productId'];
        $productInfo = $this->model_catalog_product->getProduct($productId);

        if (!$productInfo) {
            $json['error'][] = 'Мы не нашли продукта с указаным ID';
            return $this->response->setOutput(json_encode($json));
        }

        $json['data']['productId'] = $productId;
        $json['data']['name'] = $productInfo['name'];
        $json['data']['manufacturer'] = $productInfo['manufacturer'];
        $json['data']['currentCategory'] = $this->model_tool_base->getCurrentCategoryName();
        $json['data']['quantity'] = 1;

        $json['data']['isOptionsForProduct'] = (bool)$this->model_extension_module_super_offers->isOptionsForProduct($productId);

        // SPECIAL TEXT
        $json['data']['star'] = false;
        $json['data']['specialText'] = $this->model_extension_total_pro_discount->getSpecialText($productId, false);

        if (strstr($json['data']['specialText'], '*')) {
            $json['data']['star'] = true;
            $json['data']['specialText'] = trim(str_replace('*', '', $json['data']['specialText']));
        }

        if ($productInfo['image'] && is_file(DIR_IMAGE . $productInfo['image'])) {
            $json['data']['image'] = $this->model_tool_image->resize($productInfo['image'], 400, 600, true);
        } else {
            $json['data']['image'] = $this->model_tool_image->resize('no_image.png', 400, 600, true);
        }

        $json['data']['productLink'] = $this->model_extension_pro_patch_url->ajax('product/product', 'product_id=' . (int)$productId);

        $this->response->setOutput(json_encode($json));
    }

    public function getProductPreviewStock()
    {
        $parsed = $this->model_extension_pro_patch_json
            ->parseJson(file_get_contents('php://input'));

        $json = array();
        $this->response->addHeader('Content-Type: application/json');

        if (!isset($parsed['productId'])) {
            $json['error'][] = 'Обязательный параметр productId отсутствует';
            return $this->response->setOutput(json_encode($json));
        }

        $this->load->language('product/product');
        $this->load->model('catalog/product');
        $this->load->model('extension/module/super_offers');
        $this->load->model('extension/total/pro_discount');
        $this->load->model('tool/image');

        $productId = (int) $parsed['productId'];
        $productInfo = $this->model_catalog_product->getProduct($productId);

        if (!$productInfo) {
            $json['error'][] = 'Мы не нашли продукта с указаным ID';
            return $this->response->setOutput(json_encode($json));
        }

        $json['productId'] = $productId;
        $json['imageHash'] = 'default';

        if ($productInfo['image'] && is_file(DIR_IMAGE . $productInfo['image'] )) {
            $json['image'] = $this->model_tool_image->resize($productInfo['image'], 340, 450, true);
        } else {
            $json['image'] = $this->model_tool_image->resize('no_image.png', 340, 450, true);
        }

        $defaultValues = $this->model_extension_module_super_offers->getDefaultValues($productId, $productInfo);

        $json['stock']['price'] = $defaultValues['price'];
        $json['stock']['maxQuantity'] = (int) $defaultValues['max_quantity'];
        $json['stock']['inStock'] = ((int) $defaultValues['max_quantity'] > 0) ? true : false;
        $json['stock']['special'] = $defaultValues['special'];
        $json['stock']['isSpecial'] = false;
        $json['stock']['specialText'] = $this->model_extension_total_pro_discount->getSpecialText($productId, false);

        $json['stock']['star'] = false;
        if ($defaultValues['special'] != false) {
            if ((float) preg_replace('/[^0-9]/', '', $defaultValues['special']) > 0) {
                $json['stock']['star'] = true;
                $json['stock']['isSpecial'] = true;
            }
        }

        $json['options'] = $this->model_extension_module_super_offers->getOptions($productId, $json['stock']['inStock']);
        $fullCombinations = $this->model_extension_module_super_offers->getFullCombinations($productId);
        $availableCombinations = $this->extension_model->filterPossibleCombinations($fullCombinations, $json['options']);

        if (isset($parsed['options']) && is_array($parsed['options'])) {
            $json['options'] = $this->extension_model->applyOldOptionsForCurrent($parsed['options'], $json['options']);
        }

        if (isset($parsed['optionId'])
        && isset($parsed['optionValueId'])
        && isset($parsed['productOptionId'])
        && isset($parsed['productOptionValueId'])) {

            // selecting provided option value
            $json['options'] = $this->extension_model->selectOption(
                $json['options'],
                $parsed['optionId'],
                $parsed['optionValueId'],
                $parsed['productOptionId'],
                $parsed['productOptionValueId']
            );

            $activeOptions = $this->extension_model->getActiveOptionsForComparison($json['options']);
            $activeCombination = $this->extension_model->getActiveCombination($availableCombinations, $activeOptions);

            if (!$activeCombination) {
                $json['options'] = $this->extension_model->unselectAllBut(
                    $json['options'],
                    $parsed['optionId'],
                    $parsed['optionValueId'],
                    $parsed['productOptionId'],
                    $parsed['productOptionValueId']
                );
            }
        }

        if (!isset($activeCombination) || (isset($activeCombination) && !$activeCombination)) {
            $activeOptions = $this->extension_model->getActiveOptionsForComparison($json['options']);
            $activeCombination = $this->extension_model->getFirstPartialCombination($availableCombinations, $activeOptions);

            $json['options'] = $this->extension_model->applyPartialCombination(
                $json['options'],
                $activeCombination
            );
        }

        if ($activeCombination) {
            if ($activeCombination['image'] && is_file(DIR_IMAGE . $activeCombination['image'])) {
                $json['data']['image'] = $this->model_tool_image->resize($activeCombination['image'], 340, 450, true);
            }
            $json['imageHash'] = $activeCombination['imageHash'];
            $json['stock']['imageHash'] = $activeCombination['imageHash'];

            $json['stock']['price'] = $activeCombination['price'];
            $json['stock']['maxQuantity'] = (int) $activeCombination['quantity'];
        }

        $json['stock']['optionsForCart'] = $this->extension_model->getOptionsForCart($json['options']);
        $json['stock']['optionsForOneClick'] = $this->extension_model->getOptionsForOneClick($json['options']);

        $this->response->setOutput(json_encode($json));
    }

}
