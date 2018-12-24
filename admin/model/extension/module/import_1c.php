<?php
/*
 *  location: admin/model
 */
class ModelExtensionModuleImport1C extends Model
{
    private $codename = 'import_1c';
    private $route = 'extension/module/import_1c';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/setting');
    }

    public function createTables()
    {
        ///
    }

    public function dropTables()
    {
        ///
    }

    public function getScriptFiles()
    {
        $scripts = array();
        $scripts[] = "view/javascript/{$this->codename}/dist/js/{$this->codename}-vendors.js";
        $scripts[] = "view/javascript/{$this->codename}/dist/js/{$this->codename}-main.js";

        return $scripts;
    }

    public function parseJson($json)
    {
        $parsed = @json_decode($json, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return array('error' => 'Parse error.');
        }

        return $parsed === null ? array() : $parsed;
    }
}