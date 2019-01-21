<?php

class ControllerExtensionEventSuperOffers extends Controller
{
    private $codename = 'super_offers';
    private $route = 'extension/module/super_offers';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language($this->route);
        $this->load->model($this->route);

        $this->extension = json_decode(file_get_contents(DIR_SYSTEM."library/pro_hamster/extension/{$this->codename}.json"), true);

        $this->extension_model = $this->{'model_'.str_replace("/", "_", $this->route)};
    }

    public function view_options_for_product_after(&$route, &$data, &$output)
    {
        $this->load->model('extension/pro_patch/permission');
        $json = $this->model_extension_pro_patch_permission->validateRoute('catalog/option');
        if (isset($json['error'])) { return; }

        if (!isset($this->request->get['product_id'])) { return; }

        $html_dom = new d_simple_html_dom();
        $html_dom->load($output, $lowercase = true, $stripRN = false, $defaultBRText = DEFAULT_BR_TEXT);

        if ($html_dom->find('#form-product .nav-tabs', 0)) {
            $tab_name = $this->language->get('tab_product_options');
            $html_dom->find('#form-product .nav-tabs', 0)->innertext .=
                "<li><a href='#tab-super-offers' data-toggle='tab'>{$tab_name}</a></li>";
        }

        $data_ = array(
            'product_id' => (int)$this->request->get['product_id'],
            'codename'   => $this->codename,
        );

        if ($html_dom->find('#form-product .tab-content', 0)) {
            $html_dom->find('#form-product .tab-content', 0)->innertext .=
                $this->load->controller("{$this->route}/get_product_options", $data_);
        }

        // SCRIPTS & STYLES
        $scripts_src = $this->extension_model->getScriptFiles();
        $scripts = '';
        foreach ($scripts_src as $src) {
            $scripts .= "<script src='{$src}' type='text/javascript'></script>\n";
        }

        $html_dom->find('head', 0)->innertext .= $scripts;

        // GREEN BUTTON
        $html_dom->find('div[class=pull-right]', 0)->innertext =
            $this->load->controller("{$this->route}/get_product_options_green_button", array()).$html_dom->find('div[class=pull-right]', 0)->innertext;

        $output = (string)$html_dom;
    }

    public function edit_product_after(&$route, &$data, &$output)
    {
        if (isset($data[0])) {
            if (isset($data[1]['so_combination'])) {
                $this->extension_model->saveCombinations($data);
            } else {
                $this->extension_model->clearForProduct($data[0]);
            }
        }
    }

    public function list_products_before(&$route, &$data)
    {
        if (isset($data['products']) && is_array($data['products'])) {
            foreach ($data['products'] as $k => $product) {

                $state['product_id'] = (int)$product['product_id'];

                $oav = $this->extension_model->getOptionsAndValues((int)$product['product_id']);
                $state['options'] = $oav['product_options'];
                $state['option_values'] = $oav['option_values'];

                $state['combinations'] = $this->extension_model->getCombinations($state);
                $state['combinations_data'] = $this->extension_model->getCombinationsData($state);

                $q = 0;
                $p_min = 0;
                $p_max = 0;

                foreach ($state['combinations_data'] as $cd) {
                    $q += $cd['quantity'];
                    if ($cd['price'] > $p_max) {
                        $p_max = $cd['price'];
                    }
                    if ($cd['price'] < $p_min) {
                        $p_min = $cd['price'];
                    }
                }

                $data['products'][$k]['quantity'] = $q;
                if ($p_min === $p_max || $p_min === 0) {
                    $data['products'][$k]['price'] = $this->currency->format($p_max, $this->config->get('config_currency'));
                } else {
                    $p_min = $this->currency->format($p_min, $this->config->get('config_currency'));
                    $p_max = $this->currency->format($p_max, $this->config->get('config_currency'));
                    $data['products'][$k]['price'] = "{$p_min} - {$p_max}";
                }
            }
        }
    }
}
