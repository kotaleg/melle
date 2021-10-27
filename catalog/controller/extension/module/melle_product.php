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

    public function getProductFullData()
    {
        $parsed = $this->model_extension_pro_patch_json
            ->parseJson(file_get_contents('php://input'));

        $json = array();
        $this->response->addHeader('Content-Type: application/json');

        if (!isset($parsed['productId'])) {
            $json['error'][] = 'Обязательный параметр productId отсутствует';
            return $this->response->setOutput(json_encode($json));
        }

        $this->load->model('tool/image');
        $this->load->language('product/product');
        $this->load->model('catalog/product');
        $this->load->model('catalog/category');
        $this->load->model('extension/module/super_offers');
        $this->load->model('extension/total/pro_discount');
        $this->load->model('extension/module/pro_recently');

        $productId = (int) $parsed['productId'];
        $productInfo = $this->model_catalog_product->getProduct($productId);

        if (!$productInfo) {
            $json['error'][] = 'Мы не нашли продукта с указаным ID';
            return $this->response->setOutput(json_encode($json));
        }

        $json['data']['productId'] = $productId;
        $json['data']['name'] = $productInfo['h1'];
        $json['data']['manufacturer'] = $productInfo['manufacturer'];
        $json['data']['manufacturers'] = $this->model_extension_pro_patch_url
            ->ajax('product/search', 'manufacturers=' . $productInfo['manufacturer_id']);
        $json['data']['currentCategory'] = $this->model_tool_base->getCurrentCategoryName();
        $json['data']['quantity'] = 1;

        $json['data']['description'] = html_entity_decode($productInfo['description'], ENT_QUOTES, 'UTF-8');

        $this->load->model('extension/module/size_list');
        $json['data']['sizeList'] = $this->model_extension_module_size_list->getSizeList($productId);

        $this->load->model('extension/module/pro_znachek');
        $json['data']['znachek'] = $this->model_extension_module_pro_znachek->getZnachek($productInfo['znachek'], true);

        $defaultValues = $this->model_extension_module_super_offers->getDefaultValues($productId, $productInfo);

        $json['data']['options'] = $this->model_extension_module_super_offers->getOptions($productId, false);
        $fullCombinations = $this->model_extension_module_super_offers->getFullCombinations($productId);

        // SPECIAL TEXT
        $json['data']['star'] = false;
        $json['data']['specialText'] = $this->model_extension_total_pro_discount->getSpecialText($productId, false);

        if (strstr($json['data']['specialText'], '*')) {
            $json['data']['star'] = true;
            $json['data']['specialText'] = trim(str_replace('*', '', $json['data']['specialText']));
        }

        $this->load->model('catalog/review');
        $json['data']['reviewCount'] = (int) $this->model_catalog_review->getTotalReviewsByProductId($productId);
        $json['data']['ratingValue'] = (float) $defaultValues['rating'];

        $json['data']['ratingArray'] = (array) array();
        for ($i=0; $i < 5; $i++) {
            if ($defaultValues['rating'] > $i) {
                $json['data']['ratingArray'][] = true;
                continue;
            }
            $json['data']['ratingArray'][] = false;
        }

        /* EXTRA DESCRIPTION START */
        $this->load->model('extension/module/extra_description');

        $data['extra_description'] = html_entity_decode(
            $this->model_extension_module_extra_description
                ->getDescription($productId),
            ENT_QUOTES, 'UTF-8');

        $html_dom = new d_simple_html_dom();
        $html_dom->load($data['extra_description'], $lowercase = true, $stripRN = false, $defaultBRText = DEFAULT_BR_TEXT);

        $extraDescriptionPCount = 0;
        $data['extra_description_hidden'] = '';
        foreach($html_dom->find('p') as $id => $element) {
            if (utf8_strlen(strip_tags($element->innertext)) > 0) {
                $extraDescriptionPCount++;
            }
            if ($extraDescriptionPCount > 3) {
                $data['extra_description_hidden'] .= (string) $element->outertext;
                $element->outertext = '';
            }
        }
        $data['extra_description'] = (string) $html_dom;
        /* EXTRA DESCRIPTION END */

        $json['data']['sostav'] = false;
        $json['data']['den'] = false;

        foreach ($this->model_catalog_product->getProductAttributes($productId) as $group) {
            if (strcmp(trim($group['name']), 'Атрибуты') === 0) {
                foreach ($group['attribute'] as $attr) {
                    if (strcmp(trim($attr['name']), 'Состав') === 0) {
                        $json['data']['sostav'] = $attr['text'];
                    }
                    if (strcmp(trim($attr['name']), 'Ден') === 0) {
                        $json['data']['den'] = $attr['text'];
                    }
                }
            }
        }

        $json['data']['images'] = array();

        if ($productInfo['image'] && is_file(DIR_IMAGE . $productInfo['image'])) {
            $json['data']['images'][] = array(
                'zoom' => $this->model_tool_image->resize($productInfo['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width') * 2, $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height') * 2, true),
                'popup' => $this->model_tool_image->resize($productInfo['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'), true),
                'image' => $this->model_tool_image->resize($productInfo['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'), true),
                'thumb' => $this->model_tool_image->resize($productInfo['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), true),
                'enabled' => true,
                'imageHash' => md5($productInfo['image']),
            );
        }

        foreach ($this->model_catalog_product->getProductImages($productId) as $result) {
            if (!is_file(DIR_IMAGE . $result['image'])) {
                continue;
            }

            $json['data']['images'][] = array(
                'zoom' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width') * 2, $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height') * 2, true),
                'popup' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'), true),
                'image' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'), true),
                'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), true),
                'enabled' => false,
                'imageHash' => md5($result['image']),
            );
        }

        if (empty($json['data']['images'])) {
            $json['data']['images'][] = array(
                'zoom' => $this->model_tool_image->resize('no_image.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width') * 2, $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height') * 2, true),
                'popup' => $this->model_tool_image->resize('no_image.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'), true),
                'image' => $this->model_tool_image->resize('no_image.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'), true),
                'thumb' => $this->model_tool_image->resize('no_image.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), true),
                'enabled' => true,
                'imageHash' => md5('no_image.png'),
            );
        }

        $json['data']['breadcrumbs'] = array();

        $json['data']['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        if (isset($parsed['categoryPath'])) {
            $path = '';
            $parts = explode('_', (string) $parsed['categoryPath']);

            foreach ($parts as $path_id) {
                if (!$path) {
                    $path = $path_id;
                } else {
                    $path .= '_' . $path_id;
                }

                if ($category_info = $this->model_catalog_category->getCategory($path_id)) {
                    $json['data']['breadcrumbs'][] = array(
                        'text' => $category_info['name'],
                        'href' => $this->model_extension_pro_patch_url->ajax('product/category', 'path=' . $path),
                    );
                }
                unset($category_info);
            }
        }

        $json['data']['breadcrumbs'][] = array(
            'text' => $productInfo['h1'],
            'href' => $this->model_extension_pro_patch_url->ajax('product/product', '&product_id=' . $productId),
        );

        $json['data']['add_to_cart'] = $this->model_extension_pro_patch_url->ajax('checkout/cart/melle_add');
        $json['data']['buy_one_click'] = $this->model_extension_pro_patch_url->ajax('checkout/cart/melle_oneclick');
        $json['data']['getProductStock'] = $this->model_extension_pro_patch_url->ajax('extension/module/melle_product/getProductPreviewStock');

        $this->response->setOutput(json_encode($json));
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
        $json['data']['name'] = $productInfo['h1'];
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

        $this->load->model('extension/module/size_list');
        $json['data']['sizeList'] = $this->model_extension_module_size_list->getSizeList($productId);

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
