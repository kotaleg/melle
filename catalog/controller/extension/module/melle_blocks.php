<?php
/*
 *  location: catalog/controller
 */
class ControllerExtensionModuleMelleBlocks extends Controller
{
    private $codename = 'melle_blocks';
    private $route = 'extension/module/melle_blocks';
    private $type = 'module';

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

        $cacheKey = "melle.melle_blocks.module_id{$setting['moduleId']}." . serialize($setting);
        $data = $this->cache->get($cacheKey);

        if (!$data) {
            $data['moduleId'] = crc32($setting['moduleId']);

            $preparedBlocks = $this->extension_model->prepareBlocks($setting['moduleId'], $setting['height']);
            $data['blocks'] = $this->renderBlocks($preparedBlocks);
            $data['width'] = $setting['width'];
            $data['height'] = $setting['height'];
            $data['backgroundColor'] = $setting['backgroundColor'];

            $this->cache->set($cacheKey, $data);
        }

        return $this->model_extension_pro_patch_load->view($this->route, $data);
    }

    private function renderBlocks($blocks)
    {
        return array_map(function ($b) {
            return $this->renderBlock($b);
        }, $blocks);
    }

    private function renderBlock($block)
    {
        switch ($block['type']) {
            case \melle_blocks\constant::BTYPE_1:
                $type = 'one';
                break;
            case \melle_blocks\constant::BTYPE_2:
                $type = 'two';
                break;
            case \melle_blocks\constant::BTYPE_3:
                $type = 'three';
                break;
            case \melle_blocks\constant::BTYPE_4:
                $type = 'four';
                break;
        }

        $block['blockId'] = crc32(serialize($block));
        return $this->model_extension_pro_patch_load->view("{$this->route}/type_{$type}", $block);
    }
}
