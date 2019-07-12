<?php
/*
 *  location: catalog/controller
 */
class ControllerExtensionModuleMelleBlocks extends Controller
{
    private $codename = 'melle_blocks';
    private $route = 'extension/module/melle_blocks';
    private $type = 'module';

    private $setting = array();

    const BTYPE_1 = 'type-1';
    const BTYPE_2 = 'type-2';
    const BTYPE_3 = 'type-3';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language($this->route);

        $this->load->model($this->route);
        $this->load->model('extension/pro_patch/url');
        $this->load->model('extension/pro_patch/load');

        $this->extension_model = $this->{'model_'.str_replace("/", "_", $this->route)};
    }

    public function index($setting)
    {
        if (!isset($setting['moduleId'])) { return; }
        $data['moduleId'] = md5($setting['moduleId']);

        $preparedBlocks = $this->extension_model->prepareBlocks($setting['moduleId']);
        $data['blocks'] = $this->renderBlocks($preparedBlocks);

        $data['height'] = $setting['height'];

        return $this->model_extension_pro_patch_load->view($this->route, $data);
    }

    private function renderBlocks($blocks)
    {
        $rendered = array();

        foreach ($blocks as $b) {
            switch ($b['type']) {
                case self::BTYPE_1:
                    $type = 'one';
                    $class = 'col-sm-12 col-md-3';
                    break;
                case self::BTYPE_2:
                    $type = 'two';
                    $class = 'col-sm-12 col-md-6';
                    break;
                case self::BTYPE_3:
                    $type = 'three';
                    $class = 'col-sm-12 col-md-3';
                    break;
            }

            $b['blockId'] = md5(json_encode($b));
            $b['class'] = $class;
            $rendered[] = $this->model_extension_pro_patch_load->view(
                    "{$this->route}/type_{$type}", $b);
        }

        return $rendered;
    }
}