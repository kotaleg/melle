<?php
class ControllerStartupSeoUrl extends Controller {
    public function index() {
        // Add rewrite to url class
        if ($this->config->get('config_seo_url')) {
            $this->url->addRewrite($this);
        }

        // Decode URL
        if (isset($this->request->get['_route_'])) {
            $parts = explode('/', $this->request->get['_route_']);

            // remove any empty arrays from trailing
            if (utf8_strlen(end($parts)) == 0) {
                array_pop($parts);
            }

            /* IVAN MOD */
            if (count($parts) >= 2) {
                if (strcmp($parts[0], 'product') === 0) {
                    $melle_product = true;
                    unset($parts[0]);
                }
            }
            /* IVAN MOD */

            foreach ($parts as $part) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE keyword = '" . $this->db->escape($part) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

                if ($query->num_rows) {
                    $url = explode('=', $query->row['query']);

                    if ($url[0] == 'product_id') {
                        $this->request->get['product_id'] = $url[1];
                    }

                    if ($url[0] == 'category_id') {
                        if (!isset($this->request->get['path'])) {
                            $this->request->get['path'] = $url[1];
                        } else {
                            $this->request->get['path'] .= '_' . $url[1];
                        }
                    }

                    if ($url[0] == 'manufacturer_id') {
                        $this->request->get['manufacturer_id'] = $url[1];
                    }

                    if ($url[0] == 'information_id') {
                        $this->request->get['information_id'] = $url[1];
                    }

                    /* BLOG MOD START */
                    if ($url[0] == 'bm_post_id') {
                        $this->request->get['post_id'] = $url[1];
                        $this->request->get['route'] = 'extension/d_blog_module/post';
                    }
                    if ($url[0] == 'bm_category_id') {
                        $this->request->get['category_id'] = $url[1];
                        $this->request->get['route'] = 'extension/d_blog_module/category';
                    }

                    if ($query->row['query'] && $url[0] != 'information_id' && $url[0] != 'manufacturer_id' && $url[0] != 'category_id' && $url[0] != 'product_id' && $url[0] != 'bm_post_id' && $url[0] != 'bm_category_id' && $url[0] != 'discount_id') {
                        $this->request->get['route'] = $query->row['query'];
                    }
                    /* BLOG MOD END */

                    /* DISCOUNT START */
                    if ($url[0] == 'discount_id') {
                        $this->request->get['discount_id'] = $url[1];
                    }
                    /* DISCOUNT END */
                } else {
                    $this->request->get['route'] = 'error/not_found';

                    break;
                }
            }

            if (!isset($this->request->get['route'])) {
                if (isset($this->request->get['product_id'])) {
                    $this->request->get['route'] = 'product/product';
                } elseif (isset($this->request->get['path'])) {
                    $this->request->get['route'] = 'product/category';
                } elseif (isset($this->request->get['manufacturer_id'])) {
                    $this->request->get['route'] = 'product/manufacturer/info';
                } elseif (isset($this->request->get['discount_id'])) {
                    $this->request->get['route'] = 'extension/total/pro_discount/catalog';
                } elseif (isset($this->request->get['information_id'])) {
                    $this->request->get['route'] = 'information/information';
                }
            }

            /* IVAN MOD */
            if (isset($this->request->get['route'])
            && strcmp($this->request->get['route'], 'product/product') === 0) {
              $rr_params = '';
              foreach ($this->request->get as $k => $v) {
                if (in_array($k, array('product_id', 'path'))) {
                  $rr_params .= "&{$k}={$v}";
                }
              }
              $rr = str_replace('&amp;', '&', $this->url->link($this->request->get['route'], $rr_params, true));
              if (strcmp($rr, urldecode($raw_url)) !== 0) {
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: ' . $rr);
                exit;
              }
            }
            /* IVAN MOD */
        }
    }

    public function rewrite($link) {
        $url_info = parse_url(str_replace('&amp;', '&', $link));

        $url = '';

        $data = array();

        parse_str($url_info['query'], $data);

        foreach ($data as $key => $value) {
            if (isset($data['route'])) {
                if (($data['route'] == 'product/product' && $key == 'product_id') || (($data['route'] == 'product/manufacturer/info' || $data['route'] == 'product/product') && $key == 'manufacturer_id') || ($data['route'] == 'information/information' && $key == 'information_id')) {
                    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = '" . $this->db->escape($key . '=' . (int)$value) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

                    if ($query->num_rows && $query->row['keyword']) {
                        $url .= '/' . $query->row['keyword'];

                        unset($data[$key]);
                    }
                } elseif ($key == 'path') {
                    $categories = explode('_', $value);

                    foreach ($categories as $category) {
                        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = 'category_id=" . (int)$category . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

                        if ($query->num_rows && $query->row['keyword']) {
                            $url .= '/' . $query->row['keyword'];
                        } else {
                            $url = '';

                            break;
                        }
                    }

                    unset($data[$key]);
                }
            }
        }

        /* BLOG START */
        foreach ($data as $k => $v) {
            if ( isset( $data['route'] ) ) {
                if (($data['route'] == 'extension/d_blog_module/post' && $key == 'post_id')
                || ($data['route'] == 'extension/d_blog_module/category' && $key == 'category_id')) {
                    $q = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url
                        WHERE `query` = 'bm_" . $this->db->escape($key . '=' . (int)$value) . "'
                        AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

                    if ($q->num_rows && $q->row['keyword']) {
                        $url .= '/' . $q->row['keyword'];
                        unset( $data[$key] );
                    }
                }
            }
        }
        /* BLOG END */

        if ($url) {
            unset($data['route']);

            $query = '';

            if ($data) {
                foreach ($data as $key => $value) {
                    $query .= '&' . rawurlencode((string)$key) . '=' . rawurlencode((is_array($value) ? http_build_query($value) : (string)$value));
                }

                if ($query) {
                    $query = '?' . str_replace('&', '&amp;', trim($query, '&'));
                }
            }

            return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . str_replace('/index.php', '', $url_info['path']) . $url . $query;
        } else {
            return $link;
        }
    }
}
