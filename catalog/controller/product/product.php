<?php
class ControllerProductProduct extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('product/product');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $this->load->model('catalog/category');

        if (isset($this->request->get['path'])) {
            $path = '';

            $parts = explode('_', (string)$this->request->get['path']);

            $category_id = (int)array_pop($parts);

            foreach ($parts as $path_id) {
                if (!$path) {
                    $path = $path_id;
                } else {
                    $path .= '_' . $path_id;
                }

                $category_info = $this->model_catalog_category->getCategory($path_id);

                if ($category_info) {
                    $data['breadcrumbs'][] = array(
                        'text' => $category_info['name'],
                        'href' => $this->url->link('product/category', 'path=' . $path)
                    );
                }
            }

            // Set the last category breadcrumb
            $category_info = $this->model_catalog_category->getCategory($category_id);

            if ($category_info) {
                $url = '';

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
                    'text' => $category_info['name'],
                    'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url)
                );
            }
        }

        $this->load->model('catalog/manufacturer');

        if (isset($this->request->get['manufacturer_id'])) {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_brand'),
                'href' => $this->url->link('product/manufacturer')
            );

            $url = '';

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

            $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->request->get['manufacturer_id']);

            if ($manufacturer_info) {
                $data['breadcrumbs'][] = array(
                    'text' => $manufacturer_info['name'],
                    'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url)
                );
            }
        }

        if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
            $url = '';

            if (isset($this->request->get['search'])) {
                $url .= '&search=' . $this->request->get['search'];
            }

            if (isset($this->request->get['tag'])) {
                $url .= '&tag=' . $this->request->get['tag'];
            }

            if (isset($this->request->get['description'])) {
                $url .= '&description=' . $this->request->get['description'];
            }

            if (isset($this->request->get['category_id'])) {
                $url .= '&category_id=' . $this->request->get['category_id'];
            }

            if (isset($this->request->get['sub_category'])) {
                $url .= '&sub_category=' . $this->request->get['sub_category'];
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
                'text' => $this->language->get('text_search'),
                'href' => $this->url->link('product/search', $url)
            );
        }

        if (isset($this->request->get['product_id'])) {

            /* IVAN MOD */
            if (isset($this->request->get['from_opt'])) {
                $this->load->model('api/import_1c/product');
                $pid = $this->model_api_import_1c_product->getProductByImportId(
                    $this->request->get['product_id']);
                return $this->response->redirect($this->url->link('product/product', "&product_id={$pid}", true));
            }
            /* IVAN MOD */

            $product_id = (int)$this->request->get['product_id'];
        } else {
            $product_id = 0;
        }

        $this->load->model('catalog/product');

        $product_info = $this->model_catalog_product->getProduct($product_id);

        if ($product_info) {
            $url = '';

            if (isset($this->request->get['path'])) {
                $url .= '&path=' . $this->request->get['path'];
            }

            if (isset($this->request->get['filter'])) {
                $url .= '&filter=' . $this->request->get['filter'];
            }

            if (isset($this->request->get['manufacturer_id'])) {
                $url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
            }

            if (isset($this->request->get['search'])) {
                $url .= '&search=' . $this->request->get['search'];
            }

            if (isset($this->request->get['tag'])) {
                $url .= '&tag=' . $this->request->get['tag'];
            }

            if (isset($this->request->get['description'])) {
                $url .= '&description=' . $this->request->get['description'];
            }

            if (isset($this->request->get['category_id'])) {
                $url .= '&category_id=' . $this->request->get['category_id'];
            }

            if (isset($this->request->get['sub_category'])) {
                $url .= '&sub_category=' . $this->request->get['sub_category'];
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
                'text' => $product_info['name'],
                'href' => $this->url->link('product/product', $url . '&product_id=' . $this->request->get['product_id'])
            );

            $this->document->setTitle($product_info['meta_title']);
            $this->document->setDescription($product_info['meta_description']);
            $this->document->setKeywords($product_info['meta_keyword']);
            $this->document->addLink($this->url->link('product/product', 'product_id=' . $this->request->get['product_id']), 'canonical');

            $data['heading_title'] = $product_info['name'];

            $this->load->model('extension/module/melle');
            $this->load->model('extension/module/super_offers');
            $this->load->model('extension/module/pro_znachek');
            $this->load->model('extension/module/pro_recently');

            $data['recently_viewed'] = $this->model_extension_module_melle->renderOtherProducts(
                'Вы смотрели',
                $this->model_extension_module_pro_recently->getProductsPrepared()
            );

            $this->model_extension_module_pro_recently->addProduct($product_id);

            $data['znachek'] = $this->model_extension_module_pro_znachek->getZnachek($product_info['znachek'], true);
            $data['h1'] = $product_info['h1'];

            $product_state = $this->load->controller('extension/module/melle/initProduct', $product_id);
            $data['rpc'] = $this->load->controller('extension/module/melle/renderProductContent', $product_state);

            $data['text_minimum'] = sprintf($this->language->get('text_minimum'), $product_info['minimum']);
            $data['text_login'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true));

            $this->load->model('catalog/review');

            $data['tab_review'] = sprintf($this->language->get('tab_review'), $product_info['reviews']);

            $data['product_id'] = (int)$this->request->get['product_id'];
            $data['manufacturer'] = $product_info['manufacturer'];
            $data['manufacturers'] = $this->url->link('product/search', 'manufacturers=' . $product_info['manufacturer_id']);
            $data['model'] = $product_info['model'];
            $data['reward'] = $product_info['reward'];
            $data['points'] = $product_info['points'];
            $data['description'] = html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');

            /* EXTRA DESCRIPTION START */
            $this->load->model('extension/module/extra_description');

            $data['extra_description'] = html_entity_decode(
                $this->model_extension_module_extra_description
                    ->getDescription($data['product_id']),
                ENT_QUOTES, 'UTF-8');

            $html_dom = new d_simple_html_dom();
            $html_dom->load($data['extra_description'], $lowercase = true, $stripRN = false, $defaultBRText = DEFAULT_BR_TEXT);

            $extraDescriptionPCount = 0;
            $data['extra_description_hidden'] = '';
            foreach($html_dom->find('p') as $id => $element) {
                if (utf8_strlen(strip_tags($element->innertext)) > 0) {
                    $extraDescriptionPCount++;
                }
                if ($extraDescriptionPCount > 3) {
                    $data['extra_description_hidden'] .= (string) $element->outertext;
                    $element->outertext = '';
                }
            }
            $data['extra_description'] = (string) $html_dom;
            /* EXTRA DESCRIPTION END */

            if ($product_info['quantity'] <= 0) {
                $data['stock'] = $product_info['stock_status'];
            } elseif ($this->config->get('config_stock_display')) {
                $data['stock'] = $product_info['quantity'];
            } else {
                $data['stock'] = $this->language->get('text_instock');
            }

            $this->load->model('extension/module/melle_product');
            $data['images'] = $this->model_extension_module_melle_product->prepareImagesFor($data['product_id']);

            if ($product_info['minimum']) {
                $data['minimum'] = $product_info['minimum'];
            } else {
                $data['minimum'] = 1;
            }

            $data['review_status'] = $this->config->get('config_review_status');

            if ($this->config->get('config_review_guest') || $this->customer->isLogged()) {
                $data['review_guest'] = true;
            } else {
                $data['review_guest'] = false;
            }

            if ($this->customer->isLogged()) {
                $data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
            } else {
                $data['customer_name'] = '';
            }

            $data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']);
            $data['rating'] = (int)$product_info['rating'];

            $data['share'] = $this->url->link('product/product', 'product_id=' . (int)$this->request->get['product_id']);

            $data['attribute_groups'] = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);

            $data['sostav'] = false;
            $data['den'] = false;

            foreach ($data['attribute_groups'] as $group) {
                if (strcmp(trim($group['name']), 'Атрибуты') === 0) {
                    foreach ($group['attribute'] as $attr) {
                        if (strcmp(trim($attr['name']), 'Состав') === 0) {
                            $data['sostav'] = $attr['text'];
                        }
                        if (strcmp(trim($attr['name']), 'Ден') === 0) {
                            $data['den'] = $attr['text'];
                        }
                    }
                }
            }

            $data['products'] = array();

            $results = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);

            foreach ($results as $result) {
                if ($result['image']) {
                    $image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
                }

                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $price = round($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), 0);
                } else {
                    $price = false;
                }

                if ((float)$result['special']) {
                    $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                } else {
                    $special = false;
                }

                if ($this->config->get('config_tax')) {
                    $tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
                } else {
                    $tax = false;
                }

                if ($this->config->get('config_review_status')) {
                    $rating = (int)$result['rating'];
                } else {
                    $rating = false;
                }

                $data['products'][] = array(
                    'product_id'  => $result['product_id'],
                    'thumb'       => $image,
                    'name'        => $result['name'],
                    'h1'          => $result['h1'],
                    'znachek_class' => $this->model_extension_module_pro_znachek->getZnachekClass($result['znachek']),
                    'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                    'price'       => $this->model_extension_module_super_offers->getLowestPrice($result['product_id']),
                    'special'     => $special,
                    'tax'         => $tax,
                    'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
                    'rating'      => $rating,
                    'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'], true)
                );
            }

            $data['products'] = $this->model_extension_module_melle->renderOtherProducts(
                'Похожие товары',
                $data['products']
            );

            $this->model_catalog_product->updateViewed($this->request->get['product_id']);

            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('product/product', $data));
        } else {
            $url = '';

            if (isset($this->request->get['path'])) {
                $url .= '&path=' . $this->request->get['path'];
            }

            if (isset($this->request->get['filter'])) {
                $url .= '&filter=' . $this->request->get['filter'];
            }

            if (isset($this->request->get['manufacturer_id'])) {
                $url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
            }

            if (isset($this->request->get['search'])) {
                $url .= '&search=' . $this->request->get['search'];
            }

            if (isset($this->request->get['tag'])) {
                $url .= '&tag=' . $this->request->get['tag'];
            }

            if (isset($this->request->get['description'])) {
                $url .= '&description=' . $this->request->get['description'];
            }

            if (isset($this->request->get['category_id'])) {
                $url .= '&category_id=' . $this->request->get['category_id'];
            }

            if (isset($this->request->get['sub_category'])) {
                $url .= '&sub_category=' . $this->request->get['sub_category'];
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
                'href' => $this->url->link('product/product', $url . '&product_id=' . $product_id)
            );

            $this->document->setTitle($this->language->get('text_error'));

            $data['continue'] = $this->url->link('common/home');

            $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('error/not_found', $data));
        }
    }

    public function review() {
        $this->load->language('product/product');

        $this->load->model('catalog/review');

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['reviews'] = array();

        $review_total = $this->model_catalog_review->getTotalReviewsByProductId($this->request->get['product_id']);

        $results = $this->model_catalog_review->getReviewsByProductId($this->request->get['product_id'], ($page - 1) * 5, 5);

        foreach ($results as $result) {
            $data['reviews'][] = array(
                'author'     => $result['author'],
                'text'       => nl2br($result['text']),
                'rating'     => (int)$result['rating'],
                'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
            );
        }

        $pagination = new Pagination();
        $pagination->total = $review_total;
        $pagination->page = $page;
        $pagination->limit = 5;
        $pagination->url = $this->url->link('product/product/review', 'product_id=' . $this->request->get['product_id'] . '&page={page}');

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($review_total - 5)) ? $review_total : ((($page - 1) * 5) + 5), $review_total, ceil($review_total / 5));

        $this->response->setOutput($this->load->view('product/review', $data));
    }

    public function melle_add_review()
    {
        $this->load->model('extension/pro_patch/json');
        $this->load->language('product/product');

        $json['sent'] = false;
        $parsed = $this->model_extension_pro_patch_json->parseJson(file_get_contents('php://input'));

        if (isset($parsed['form']) && is_array($parsed['form'])) {

            if ((utf8_strlen($parsed['form']['name']) < 3) || (utf8_strlen($parsed['form']['name']) > 25)) {
                $json['form_error']['name'] = $this->language->get('error_name');
            }

            if ((utf8_strlen($parsed['form']['message']) < 10) || (utf8_strlen($parsed['form']['message']) > 1000)) {
                $json['form_error']['message'] = $this->language->get('error_text');
            }

            if (empty($parsed['form']['rating']) || $parsed['form']['rating'] < 0 || $parsed['form']['rating'] > 5) {
                $json['form_error']['rating'] = $this->language->get('error_rating');
            }

            if (!isset($json['form_error'])) {
                $this->load->model('catalog/review');
                $this->model_catalog_review->addReview($parsed['form']['product_id'], array(
                    'name' => $parsed['form']['name'],
                    'text' => $parsed['form']['message'],
                    'rating' => $parsed['form']['rating'],
                    'options' => $json['form_error']['options'],
                ));
                $json['sent'] = true;
                $json['success'][] = $this->language->get('text_success');
            }

        } else {
            $json['error'][] = $this->language->get('error_no_fields');
        }

        $this->response->setOutput(json_encode($json));
    }

    public function write() {
        $this->load->language('product/product');

        $json = array();

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
                $json['error'] = $this->language->get('error_name');
            }

            if ((utf8_strlen($this->request->post['text']) < 25) || (utf8_strlen($this->request->post['text']) > 1000)) {
                $json['error'] = $this->language->get('error_text');
            }

            if (empty($this->request->post['rating']) || $this->request->post['rating'] < 0 || $this->request->post['rating'] > 5) {
                $json['error'] = $this->language->get('error_rating');
            }

            // Captcha
            if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
                $captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

                if ($captcha) {
                    $json['error'] = $captcha;
                }
            }

            if (!isset($json['error'])) {
                $this->load->model('catalog/review');

                $this->model_catalog_review->addReview($this->request->get['product_id'], $this->request->post);

                $json['success'] = $this->language->get('text_success');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function getRecurringDescription() {
        $this->load->language('product/product');
        $this->load->model('catalog/product');

        if (isset($this->request->post['product_id'])) {
            $product_id = $this->request->post['product_id'];
        } else {
            $product_id = 0;
        }

        if (isset($this->request->post['recurring_id'])) {
            $recurring_id = $this->request->post['recurring_id'];
        } else {
            $recurring_id = 0;
        }

        if (isset($this->request->post['quantity'])) {
            $quantity = $this->request->post['quantity'];
        } else {
            $quantity = 1;
        }

        $product_info = $this->model_catalog_product->getProduct($product_id);

        $recurring_info = $this->model_catalog_product->getProfile($product_id, $recurring_id);

        $json = array();

        if ($product_info && $recurring_info) {
            if (!$json) {
                $frequencies = array(
                    'day'        => $this->language->get('text_day'),
                    'week'       => $this->language->get('text_week'),
                    'semi_month' => $this->language->get('text_semi_month'),
                    'month'      => $this->language->get('text_month'),
                    'year'       => $this->language->get('text_year'),
                );

                if ($recurring_info['trial_status'] == 1) {
                    $price = $this->currency->format($this->tax->calculate($recurring_info['trial_price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    $trial_text = sprintf($this->language->get('text_trial_description'), $price, $recurring_info['trial_cycle'], $frequencies[$recurring_info['trial_frequency']], $recurring_info['trial_duration']) . ' ';
                } else {
                    $trial_text = '';
                }

                $price = $this->currency->format($this->tax->calculate($recurring_info['price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

                if ($recurring_info['duration']) {
                    $text = $trial_text . sprintf($this->language->get('text_payment_description'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
                } else {
                    $text = $trial_text . sprintf($this->language->get('text_payment_cancel'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
                }

                $json['success'] = $text;
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
