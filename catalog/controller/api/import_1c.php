<?php
class ControllerApiImport1C extends Controller
{
    private $codename = 'import_1c';
    private $route = 'tool/import_1c';
    private $setting_route = 'extension/module/import_1c';

    function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model($this->route);

        $this->extension_model = $this->{'model_'.str_replace("/", "_", $this->route)};
    }

    public function index()
    {
        $time_start = microtime(true);
        $r = $this->extension_model->test();

        $time_end = microtime(true);
        $time = $time_end - $time_start;
        echo "<pre>"; print_r($time); echo "</pre>";
        echo "<pre>"; print_r($r); echo "</pre>";exit;
    }
}