<?php
/*
 *  location: catalog/controller
 */
class ControllerExtensionModuleExportCombinations extends Controller
{
    private $codename = 'export_combinations';
    private $route = 'extension/module/export_combinations';
    private $type = 'module';

    private $setting = array();

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language($this->route);

        $this->load->model($this->route);
        $this->load->model('extension/pro_patch/load');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/language');
        $this->load->model('extension/pro_patch/setting');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);
        $this->extension_model = $this->{'model_'.str_replace("/", "_", $this->route)};
    }

    public function index()
    {
        $parsed = $this->model_extension_pro_patch_json->parseJson(file_get_contents('php://input'));

        $json['codename'] = $this->codename;

        if (is_array($parsed)) {
            $json = $this->extension_model->fillCombinations($parsed);
        } else {
            $json['message'][] = 'Missing reuired data';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
