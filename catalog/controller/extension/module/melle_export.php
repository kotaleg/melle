<?php
/*
 *  location: catalog/controller
 */
class ControllerExtensionModuleMelleExport extends Controller
{
    private $codename = 'melle';
    private $route = 'extension/module/melle_export';
    private $type = 'module';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language($this->route);

        $this->load->model($this->route);
        $this->load->model('extension/pro_patch/url');
        $this->load->model('extension/pro_patch/load');
        $this->load->model('extension/pro_patch/user');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/setting');
        $this->load->model('tool/base');
        $this->load->model('extension/module/super_offers');

        $this->extension_model = $this->{'model_'.str_replace("/", "_", $this->route)};
    }

    public function index()
    {
        $json['exported'] = false;
        if (isset($this->request->get['export_type'])) {
            # code...
        }


    }

    private function yandex()
    {

    }

}