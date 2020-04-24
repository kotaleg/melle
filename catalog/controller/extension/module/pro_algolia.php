<?php
/*
 *  location: catalog/controller
 */
class ControllerExtensionModulePROAlgolia extends Controller
{
    private $codename = 'pro_algolia';
    private $route = 'extension/module/pro_algolia';
    private $type = 'module';

    private $error = array();
    private $setting = array();

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language($this->route);

        $this->load->model($this->route);
        $this->load->model('extension/pro_patch/load');
        $this->load->model('extension/pro_patch/language');
        $this->load->model('extension/pro_patch/setting');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);
        $this->extension_model = $this->{'model_'.str_replace("/", "_", $this->route)};
    }

    public function index()
    {
        $json['codename'] = $this->codename;

        if ($this->setting['status']) {
            $json = array_merge($json, $this->extension_model->work());
        } else {
            $json['message'][] = "{$this->codename} module disabled";
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
