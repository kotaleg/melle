<?php
class ControllerCommonHome extends Controller {
    public function index() {
        $this->document->setTitle($this->config->get('config_meta_title'));
        $this->document->setDescription($this->config->get('config_meta_description'));
        $this->document->setKeywords($this->config->get('config_meta_keyword'));

        if (isset($this->request->get['route'])) {
            if ($this->request->server['HTTPS']) {
                $server = $this->config->get('config_ssl');
            } else {
                $server = $this->config->get('config_url');
            }
            $this->document->addLink($server, 'canonical');
        }

        // $data['column_left'] = $this->load->controller('common/column_left');
        // $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        // IMPORT OLD CUSTOMERS
        // $this->load->model('api/import_1c/customer');
        // $this->model_api_import_1c_customer->addOldUsers();

        // IMPORT COLOR CUBS
        // $this->load->model('api/import_1c/option');
        // $this->model_api_import_1c_option->importColorCubs();

        // IMPORT REDIRECTS
        // $this->load->model('api/import_1c/seo');
        // $this->model_api_import_1c_seo->parseRedirects();

        // $this->reindexProducts();

        $this->response->setOutput($this->load->view('common/home', $data));
    }

    private function reindexProducts()
                {
                    $this->load->model("extension/module/pro_algolia");
                    
                    $enabledProducts = $this->db->query("SELECT `product_id`, `import_id`
                        FROM `". DB_PREFIX . "product`
                        WHERE `status` = '". true ."'")->rows;

                    foreach ($enabledProducts as $product) {
                        $this->model_extension_module_pro_algolia->queueSaveProduct($product['product_id']);
                    }

                    $disabledProducts = $this->db->query("SELECT `product_id`, `import_id`
                        FROM `". DB_PREFIX . "product`
                        WHERE `status` = '". false ."'")->rows;

                    $disabledProducts = array_map(function($row) {
                        return $row['product_id'];
                    }, $disabledProducts);

                    foreach ($enabledProducts as $product) {
                        $this->model_extension_module_pro_algolia->queueDeleteProduct($product['product_id']);
                    }
                }
}
