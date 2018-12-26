<?php
class ModelToolImport1C extends Model
{
    private $codename = 'import_1c';
    private $route = 'tool/import_1c';
    private $setting_route = 'extension/module/import_1c';

    private $exchange_path;

    function __construct($registry)
    {
        parent::__construct($registry);

        $this->import_1c = new \import_1c\import_1c();
        $this->exchange_path = $this->getRootPath() . 'protected/runtime/exchange/';

        $this->files = array(
            'import.xml',
            'offers.xml',
        );
    }

    public function test()
    {
        // $this->import_1c->openFile("{$this->exchange_path}import.xml");
        $this->import_1c->openFile("{$this->exchange_path}offers.xml");
        $this->import_1c->test();
    }

    private function getRootPath()
    {
        return dirname(DIR_SYSTEM).'/';
    }
}
