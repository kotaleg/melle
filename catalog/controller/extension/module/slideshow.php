<?php
class ControllerExtensionModuleSlideshow extends Controller {
    public function index($setting) {
        static $module = 0;

        $this->load->model('design/banner');
        $this->load->model('tool/image');
        $this->load->model('tool/base');

        $this->document->addStyle('catalog/view/javascript/melle/query/swiper/swiper.min.css');
        // $this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
        $this->document->addScript('catalog/view/javascript/melle/query/swiper/swiper.min.js');

        $data['banners'] = array();

        $results = $this->model_design_banner->getBanner($setting['banner_id']);

        foreach ($results as $result) {
            if (is_file(DIR_IMAGE . $result['image'])) {
                $data['banners'][$result['sort_order']][mb_strtolower($result['title'])] = array(
                    'title' => $result['title'],
                    'link'  => $result['link'],
                    'image' => $this->model_tool_base->getBase() . 'image/' . $result['image'],
                );
            }
        }

        $data['module'] = $module++;

        return $this->load->view('extension/module/slideshow', $data);
    }
}