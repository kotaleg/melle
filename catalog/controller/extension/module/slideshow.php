<?php
class ControllerExtensionModuleSlideshow extends Controller {
    public function index($setting) {
        static $module = 0;

        $this->load->model('design/banner');
        $this->load->model('tool/image');
        $this->load->model('tool/base');

        $data['banners'] = array();

        // IVAN MOD START
        if (isset($setting['selected_banner'])) {
            foreach ($setting['selected_banner'] as $bannerId) {
                $results = $this->model_design_banner->getBanner($bannerId);

                foreach ($results as $result) {
                    if (is_file(DIR_IMAGE . $result['image'])) {
                        $data['banners'][$result['sort_order']][mb_strtolower($result['title'])] = array(
                            'title' => $result['title'],
                            'link'  => $result['link'],
                            'image' => $this->model_tool_base->getBase() . 'image/' . $result['image'],
                        );
                    }
                }
            }
        }
        // IVAN MOD END

        $data['module'] = $module++;

        // SET STATE
        $this->document->addState("melle_slideshow_{$data['module']}", json_encode($data['banners']));

        return $this->load->view('extension/module/slideshow', $data);
    }
}
