<?php
class ControllerExtensionFeedGoogleSitemap extends Controller {
    public function index() {
        if ($this->config->get('feed_google_sitemap_status')) {
            $output  = '<?xml version="1.0" encoding="UTF-8"?>';
            $output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

            $this->load->model('tool/base');

            $output .= '<url>';
            $output .= '  <loc>' . $this->model_tool_base->getBase() . '</loc>';
            $output .= '  <changefreq>daily</changefreq>';
            $output .= '  <priority>1.0</priority>';
            $output .= '</url>';

            $this->load->model('catalog/product');
            $this->load->model('tool/image');

            $products = $this->model_catalog_product->getProducts();

            foreach ($products as $product) {
                if ($product['image']) {
                    $output .= '<url>';
                    $output .= '  <loc>' . $this->url->link('product/product', 'product_id=' . $product['product_id'], true) . '</loc>';
                    $output .= '  <changefreq>weekly</changefreq>';
                    $output .= '  <lastmod>' . date('Y-m-d\TH:i:sP', strtotime($product['date_modified'])) . '</lastmod>';
                    $output .= '  <priority>0.8</priority>';
                    // $output .= '  <image:image>';
                    // $output .= '  <image:loc>' . $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')) . '</image:loc>';
                    // $output .= '  <image:caption>' . $product['name'] . '</image:caption>';
                    // $output .= '  <image:title>' . $product['name'] . '</image:title>';
                    // $output .= '  </image:image>';
                    $output .= '</url>';
                }
            }

            $this->load->model('catalog/category');

            $output .= $this->getCategories(0);

            // $this->load->model('catalog/manufacturer');

            // $manufacturers = $this->model_catalog_manufacturer->getManufacturers();

            // foreach ($manufacturers as $manufacturer) {
            //     $output .= '<url>';
            //     $output .= '  <loc>' . $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer['manufacturer_id']) . '</loc>';
            //     $output .= '  <changefreq>yearly</changefreq>';
            //     $output .= '  <priority>0.6</priority>';
            //     $output .= '</url>';

                // $products = $this->model_catalog_product->getProducts(array('filter_manufacturer_id' => $manufacturer['manufacturer_id']));

                // foreach ($products as $product) {
                //     $output .= '<url>';
                //     $output .= '  <loc>' . $this->url->link('product/product', 'manufacturer_id=' . $manufacturer['manufacturer_id'] . '&product_id=' . $product['product_id']) . '</loc>';
                //     $output .= '  <changefreq>hourly</changefreq>';
                //     $output .= '  <priority>'. round((float) $product['sitemap_p'], 1) .'</priority>';
                //     $output .= '</url>';
                // }
            // }

            $this->load->model('catalog/information');

            $informations = $this->model_catalog_information->getInformations();

            foreach ($informations as $information) {
                $output .= '<url>';
                $output .= '  <loc>' . $this->url->link('information/information', 'information_id=' . $information['information_id'], true) . '</loc>';
                $output .= '  <changefreq>yearly</changefreq>';
                $output .= '  <priority>0.6</priority>';
                $output .= '</url>';
            }

            // BLOG
            $output .= $this->getBlog();

            $output .= '</urlset>';

            $this->response->addHeader('Content-Type: application/xml');
            $this->response->setOutput($output);
        }
    }

    protected function getCategories($parent_id, $current_path = '') {
        $output = '';

        $results = $this->model_catalog_category->getCategories($parent_id);

        foreach ($results as $result) {
            if (!$current_path) {
                $new_path = $result['category_id'];
            } else {
                $new_path = $current_path . '_' . $result['category_id'];
            }

            $output .= '<url>';
            $output .= '  <loc>' . $this->url->link('product/category', 'path=' . $new_path) . '</loc>';
            $output .= '  <changefreq>daily</changefreq>';
            $output .= '  <priority>0.9</priority>';
            $output .= '</url>';

            // $products = $this->model_catalog_product->getProducts(array('filter_category_id' => $result['category_id']));

            // foreach ($products as $product) {
            //     $output .= '<url>';
            //     $output .= '  <loc>' . $this->url->link('product/product', 'path=' . $new_path . '&product_id=' . $product['product_id']) . '</loc>';
            //     $output .= '  <changefreq>hourly</changefreq>';
            //     $output .= '  <priority>'. round((float) $product['sitemap_p'], 1) .'</priority>';
            //     $output .= '</url>';
            // }

            $output .= $this->getCategories($result['category_id'], $new_path);
        }

        return $output;
    }

    private function getBlog()
    {
        $output = '';

        $this->load->model('extension/module/d_blog_module');
        $this->load->model('extension/d_blog_module/post');
        $this->load->model('extension/d_blog_module/category');

        $config_file = $this->model_extension_module_d_blog_module->getConfigFile('d_blog_module', array('lite', 'light', 'free'));
        $setting = $this->model_extension_module_d_blog_module->getConfigData('d_blog_module', 'd_blog_module' . '_setting', $this->config->get('config_store_id'), $config_file);

        $category_id = $setting['category']['main_category_id'];
        $category_info = $this->model_extension_d_blog_module_category->getCategory($category_id);

        $output .= '<url>';
        $output .= '  <loc>' . $this->url->link('extension/d_blog_module/category', 'category_id=' . $category_id, true) . '</loc>';
        $output .= '  <changefreq>yearly</changefreq>';
        $output .= '  <priority>0.6</priority>';
        $output .= '</url>';

        $posts = $this->model_extension_d_blog_module_post->getPosts();

        foreach ($posts as $p) {
            $output .= '<url>';
            $output .= '  <loc>' . $this->url->link('extension/d_blog_module/post', 'post_id=' . $p['post_id'], true) . '</loc>';
            $output .= '  <changefreq>yearly</changefreq>';
            $output .= '  <priority>0.6</priority>';
            $output .= '</url>';
        }

        return  $output;
    }
}
