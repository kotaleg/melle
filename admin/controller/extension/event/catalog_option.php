<?php

class ControllerExtensionEventCatalogOption extends Controller
{
    private $codename = 'catalog_option';
    private $route = 'extension/module/catalog_option';

    private $catalogOptionRoute = 'catalog/option';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model($this->route);
        $this->load->model('extension/pro_patch/url');
    }

    public function view_catalog_option_list_after(&$route, &$data, &$output)
    {
        $output = $this->load->controller("{$this->route}/option");
    }

    public function view_catalog_option_form_before(&$route, &$data)
    {
        return $this->response->redirect(
            $this->model_extension_pro_patch_url->link($this->catalogOptionRoute));
    }
}
