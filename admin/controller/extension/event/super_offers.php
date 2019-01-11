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
}
