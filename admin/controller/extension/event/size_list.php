<?php

class ControllerExtensionEventSizeList extends Controller
{
    private $codename = 'size_list';
    private $route = 'extension/module/size_list';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language($this->route);
        $this->load->model($this->route);

        $this->extension_model = $this->{'model_'.str_replace("/", "_", $this->route)};
    }

    public function view_size_list_for_product_after(&$route, &$data, &$output)
    {
        $this->load->model('extension/pro_patch/permission');
        $json = $this->model_extension_pro_patch_permission->validateRoute('catalog/option');
        if (isset($json['error'])) { return; }

        if (!isset($this->request->get['product_id'])) { return; }

        $html_dom = new d_simple_html_dom();
        $html_dom->load($output, $lowercase = true, $stripRN = false, $defaultBRText = DEFAULT_BR_TEXT);

        if ($html_dom->find('#form-product .nav-tabs', 0)) {
            $tab_name = $this->language->get('heading_title_main');
            $html_dom->find('#form-product .nav-tabs', 0)->innertext .=
                "<li><a href='#tab-size-list' data-toggle='tab'>{$tab_name}</a></li>";
        }

        $data_ = array(
            'product_id' => (int)$this->request->get['product_id'],
            'codename'   => $this->codename,
        );

        if ($html_dom->find('#form-product .tab-content', 0)) {
            $html_dom->find('#form-product .tab-content', 0)->innertext .=
                $this->load->controller("{$this->route}/get_size_list", $data_);
        }

        // SCRIPTS & STYLES
        $scripts_src = $this->extension_model->getScriptFiles();
        $scripts = '';
        foreach ($scripts_src as $src) {
            $scripts .= "<script src='{$src}' type='text/javascript'></script>\n";
        }

        $html_dom->find('head', 0)->innertext .= $scripts;

        $output = (string)$html_dom;
    }

    public function edit_product_after(&$route, &$data, &$output)
    {
        if (isset($data[0])) {
            if (isset($data[1]['size_list_data'])) {
                $this->extension_model->saveSizeListForProduct($data);
            }
        }
    }
}
