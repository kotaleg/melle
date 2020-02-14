<?php
/*
 *  location: admin/model
 */
class ModelExtensionModuleCatalogOption extends Model
{
    private $codename = 'catalog_option';
    private $route = 'extension/module/catalog_option';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/setting');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/db');
        $this->load->model('extension/pro_patch/url');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);
    }

    public function getScriptFiles()
    {
        if (isset($this->setting['debug']) && $this->setting['debug']) {
            $rand = '?'.rand(777, 999);
        } else { $rand = ''; }

        $scripts = array();
        $scripts[] = "view/javascript/{$this->codename}/dist/{$this->codename}.js{$rand}";

        return $scripts;
    }

    public function createTables()
    {
        //
    }

    public function dropTables()
    {
        //
    }

    private function log($message)
    {
        $this->log->write(strtoupper($this->codename)." :: {$message}");
    }
}