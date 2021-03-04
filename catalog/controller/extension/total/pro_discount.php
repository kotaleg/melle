<?php
/*
 *  location: catalog/controller
 */
class ControllerExtensionTotalProDiscount extends Controller
{
    private $codename = 'pro_discount';
    private $route = 'extension/total/pro_discount';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language($this->route);
        $this->load->model($this->route);

        $this->extension_model = $this->{'model_'.str_replace("/", "_", $this->route)};
    }

    public function catalog()
    {
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        if (isset($this->request->get['discount_id'])) {
            $discount_id = (int)$this->request->get['discount_id'];
        } else {
            $discount_id = 0;
        }

        $discount_info = $this->extension_model->getDiscountData($discount_id);

        if ($discount_info) {
            $this->document->setTitle($discount_info['meta_title'] ? $discount_info['meta_title'] : $discount_info['name']);
            $this->document->setDescription($discount_info['meta_description']);
            $this->document->setKeywords($discount_info['meta_keywords']);

            $data['heading_title'] = $discount_info['name'];

            // Set the last category breadcrumb
            $data['breadcrumbs'][] = array(
                'text' => $discount_info['name'],
                'href' => $this->url->link($this->route, 'discount_id=' . $discount_id)
            );

            $catalog_state = $this->load->controller('extension/module/melle/initCatalog', $data);
            $data['rcc'] = $this->load->controller('extension/module/melle/renderCatalogContent', $catalog_state);

            $filter_state = $this->load->controller('extension/module/melle/initFilter');
            $data['rfc'] = $this->load->controller('extension/module/melle/renderFilterContent', $filter_state);

            $this->load->model('extension/module/pro_recently');
            $data['recently_viewed'] = $this->model_extension_module_pro_recently->getProductsPrepared();

            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('product/category', $data));
        } else {
            $url = '';

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_error'),
                'href' => $this->url->link('product/category', $url)
            );

            $this->document->setTitle($this->language->get('text_error'));

            $data['continue'] = $this->url->link('common/home');

            $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('error/not_found', $data));
        }
    }
}
