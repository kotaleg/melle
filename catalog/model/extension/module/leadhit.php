<?php
/*
 *  location: catalog/model
 */
class ModelExtensionModuleLeadhit extends Model
{
    private $codename = 'leadhit';
    private $route = 'extension/module/leadhit';

    private $setting = array();

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/url');
        $this->load->model('extension/pro_patch/load');
        $this->load->model('extension/pro_patch/user');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/setting');
        $this->load->model('tool/base');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);
    }

    public function getSetting()
    {
        return $this->setting;
    }
}
