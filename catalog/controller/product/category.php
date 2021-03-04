<?php
class ControllerProductCategory extends Controller {
    public function index() {
        $this->load->language('product/category');

        $this->load->model('catalog/category');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        if (isset($this->request->get['path'])) {
            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }

            $path = '';

            $parts = explode('_', (string)$this->request->get['path']);

            /* REDIRECT TO THE LAST CATEGORY */
            if (count($parts) > 1) {
                $category_id = (int)array_pop($parts);
                $this->request->get['path'] = $category_id;
                $this->response->redirect($this->url->link('product/category', "path={$category_id}", true));
            }
            /* REDIRECT TO THE LAST CATEGORY */

            $category_id = (int)array_pop($parts);

            foreach ($parts as $path_id) {
                if (!$path) {
                    $path = (int)$path_id;
                } else {
                    $path .= '_' . (int)$path_id;
                }

                $category_info = $this->model_catalog_category->getCategory($path_id);

                if ($category_info) {
                    $data['breadcrumbs'][] = array(
                        'text' => $category_info['name'],
                        'href' => $this->url->link('product/category', 'path=' . $path . $url)
                    );
                }
            }
        } else {
            $category_id = 0;
        }

        $category_info = $this->model_catalog_category->getCategory($category_id);

        if ($category_info) {
            /* RETAIL R START */
            $this->request->get['rr_category_id'] = (int) $category_id;
            /* RETAIL R END */

            $this->document->setTitle($category_info['meta_title']);
            $this->document->setDescription($category_info['meta_description']);
            $this->document->setKeywords($category_info['meta_keyword']);

            $data['heading_title'] = $category_info['name'];

            // Set the last category breadcrumb
            $data['breadcrumbs'][] = array(
                'text' => $category_info['name'],
                'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'])
            );

            $catalog_state = $this->load->controller('extension/module/melle/initCatalog', $data);
            $data['rcc'] = $this->load->controller('extension/module/melle/renderCatalogContent', $catalog_state);

            $filter_state = $this->load->controller('extension/module/melle/initFilter');
            $data['rfc'] = $this->load->controller('extension/module/melle/renderFilterContent', $filter_state);

            $this->load->model('extension/module/pro_recently');
            $this->load->model('extension/module/melle');

            $data['recently_viewed'] = $this->model_extension_module_melle->renderOtherProducts(
                'Вы смотрели',
                $this->model_extension_module_pro_recently->getProductsPrepared()
            );

            $data['description'] = html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8');

            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('product/category', $data));
        } else {
            $url = '';

            if (isset($this->request->get['path'])) {
                $url .= '&path=' . $this->request->get['path'];
            }

            if (isset($this->request->get['filter'])) {
                $url .= '&filter=' . $this->request->get['filter'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
            }

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


    public function melle_get()
    {
        $this->load->model('extension/pro_patch/url');
        $this->load->model('extension/pro_patch/json');

        $json = array();
        $parsed = $this->model_extension_pro_patch_json->parseJson(file_get_contents('php://input'));

        if (isset($parsed['filter_data']) && is_array($parsed['filter_data'])) {
            $this->load->model('catalog/super');

            $json = array_merge($json, $this->model_catalog_super->getProducts($parsed['filter_data']));
            $json['filter_data'] = $this->model_catalog_super->getFilterValues($parsed['filter_data']);
        }

        $this->response->setOutput(json_encode($json));
    }
}
