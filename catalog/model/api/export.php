<?php
class ModelApiExport extends Model
{
    private $codename = 'export';
    private $route = 'api/export';
    private $setting_route = 'extension/module/export';

    private $export_path;

    function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/db');
        $this->load->model('api/import_1c/progress');

        $this->export_path = $this->getRootPath() . 'exports/';
        $this->extra = $this->model_api_import_1c_progress->getExtra();
    }

    private function getRootPath()
    {
        return dirname(DIR_SYSTEM).'/';
    }

    public function actionSeoExport()
    {
        $file = $this->export_path . 'seo.xml';
        if (is_file($file)) {
            @unlink($file);
        }
        $this->createPath($file);

        $f = fopen($file, 'w');

        $this->_str = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        fwrite($f, $this->_str);

        $pcount = 0;
        $no_price_count = 0;
        $this->load->model('catalog/product');
        $this->load->model('api/import_1c/product');
        $this->load->model('extension/module/super_offers');

        $this->_str = "<Товары>\n";
        foreach ($this->model_api_import_1c_product->getAllProductsIds() as $pid) {
            $product_data = $this->model_catalog_product->getProduct($pid);
            if ($product_data) {

                $dp = $this->model_extension_module_super_offers->getDefaultValues($product_data['product_id'], $product_data);

                if ((int)$dp['price']) {
                    $this->_str .= "<item id=\"{$product_data['product_id']}\">" .
                        "   <Наименование>" . htmlspecialchars($product_data['name']) . "</Наименование>\n" .
                        "   <HeadTitle>" . htmlspecialchars($product_data['meta_title']) . "</HeadTitle>\n" .
                        "   <seoH1>" . htmlspecialchars($product_data['h1']) . "</seoH1>\n" .
                        "   <description>" . htmlspecialchars($product_data['meta_description']) . "</description>\n" .
                        "   <keyWords>" . htmlspecialchars($product_data['meta_keyword']) . "</keyWords>\n";
                    $this->_str .= "</item>\n";
                    $pcount++;
                } else {
                    $no_price_count++;
                }

            }
        }
        $this->_str .= "</Товары>";
        fwrite($f, $this->_str);

        fclose($f);

        $json['message'][] = "Обработано {$pcount} товаров.";

        if ($no_price_count) {
            $json['message'][] = "Товаров без цены {$no_price_count}";
        }

        return $json;
    }

    public function actionGoogleExport()
    {
        $file = $this->export_path . 'google.xml';
        if (is_file($file)) {
            @unlink($file);
        }
        $this->createPath($file);

        $this->load->model('tool/base');
        $base_path = $this->model_tool_base->getBase();

        $f = fopen($file, 'w');

        $this->_str = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
            "<rss xmlns:g=\"http://base.google.com/ns/1.0\" version=\"2.0\">\n" .
            "<title>". htmlspecialchars($this->config->get('config_meta_title')) ."</title>\n" .
            "<link>" . htmlspecialchars($base_path) . "</link>\n" .
            "<description>" . htmlspecialchars($this->config->get('config_meta_description')) . "</description>\n" .
            "<channel>\n";
        fwrite($f, $this->_str);

        $pcount = 0;
        $no_price_count = 0;
        $this->load->model('tool/base');
        $this->load->model('catalog/product');
        $this->load->model('api/import_1c/product');
        $this->load->model('extension/module/super_offers');

        foreach ($this->model_api_import_1c_product->getAllProductsIds() as $pid) {
            $product_data = $this->model_catalog_product->getProduct($pid);
            if ($product_data) {

                $dp = $this->model_extension_module_super_offers->getDefaultValues($product_data['product_id'], $product_data);

                // PASS PRODUCTS
                $pass = false;
                if (isset($dp['price'])) {
                    if ($dp['min_quantity'] <= 0) { $pass = true; }
                    if ($dp['price'] <= 0) { $pass = true; }
                } else { $pass = true; }

                if ($pass === false) {
                    $this->_str = '';

                    $price = (int)preg_replace('/\s+/', '', $dp['price']);
                    $special = (int)preg_replace('/\s+/', '', $dp['special']);
                    if ($special !== false && $special > 0) {
                        $price = $special;
                    }

                    $seo_url = $this->getSeoUrl($product_data['product_id']);
                    $breadcrumbs = $this->getBreadcrumbs($product_data['product_id']);

                    if ($product_data['image']) {
                        $image = $base_path . 'image/' . $product_data['image'];
                    } else {
                        $image = $base_path . 'image/placeholder.png';
                    }

                    $this->_str .= "<item>\n<g:id>{$product_data['product_id']}</g:id>\n" .
                        ((!empty($product_data['h1'])) ? "<g:title>" . htmlspecialchars($product_data['h1']) . "</g:title>\n" : "<g:title>" . htmlspecialchars($product_data['name']) . "</g:title>\n").
                        ((!empty($product_data['meta_description'])) ? "<g:description>" . htmlspecialchars(strip_tags($product_data['meta_description'])) . "</g:description>\n" : "<g:description>Описание у товара скоро появится</g:description>\n").
                        "<g:link>{$seo_url}</g:link>\n" .
                        "<g:image_link>{$image}</g:image_link>\n" .
                        "<g:condition>new</g:condition>".
                        "<g:availability>in stock</g:availability>".
                        "<g:product_type>". htmlspecialchars($breadcrumbs) ."</g:product_type>".
                        ((!empty($product_data['manufacturer'])) ? "<g:brand>" . htmlspecialchars($product_data['manufacturer']) . "</g:brand>\n" : "") .
                        "<g:price>". $price ." RUB</g:price>\n";

                    $this->_str .= "</item>\n";
                    fwrite($f, $this->_str);
                    unset($this->_str);

                    $pcount++;
                } else {
                    $no_price_count++;
                }

            }
        }

        $this->_str = "</channel>\n" .
            "</rss>";

        fwrite($f, $this->_str);
        fclose($f);

        $json['message'][] = "Обработано {$pcount} товаров.";

        if ($no_price_count) {
            $json['message'][] = "Товаров без цены {$no_price_count}";
        }

        return $json;
    }

    public function actionYandexExport()
    {
        $file = $this->export_path . 'yandex.xml';
        if (is_file($file)) {
            @unlink($file);
        }
        $this->createPath($file);

        $this->load->model('tool/base');
        $base_path = $this->model_tool_base->getBase();

        $f = fopen($file, 'w');

        $this->_str = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
            "<!DOCTYPE yml_catalog SYSTEM \"shops.dtd\">\n" .
            "<yml_catalog date=\"" . date('Y-m-d H:i') . "\">\n" .
            "<shop>\n" .
            "<name>" . htmlspecialchars($this->config->get('config_meta_title')) . "</name>\n" .
            "<company>" . htmlspecialchars($this->config->get('config_meta_title')) . "</company>\n" .
            "<url>{$base_path}</url>\n" .
            "<currencies>\n" .
            "<currency id=\"RUR\" rate=\"1\" plus=\"0\"/>\n" .
            "</currencies>\n" .
            "<categories>\n";
        fwrite($f, $this->_str);

        foreach ($this->getCategories() as $cat) {
            $group_name = htmlspecialchars($cat['name']);
            $this->_str = "<category id=\"{$cat['category_id']}\">{$group_name}</category>\n";
            $this->setTree($cat['category_id']);
            fwrite($f, $this->_str);
        }

        $this->_str = "</categories>\n" .
            "<offers>\n";
        fwrite($f, $this->_str);

        $pcount = 0;
        $no_price_count = 0;

        $this->load->model('catalog/product');
        $this->load->model('api/import_1c/product');
        $this->load->model('extension/module/super_offers');

        foreach ($this->model_api_import_1c_product->getAllProductsIds() as $pid) {
            $product_data = $this->model_catalog_product->getProduct($pid);
            if ($product_data) {

                $dp = $this->model_extension_module_super_offers->getDefaultValues($product_data['product_id'], $product_data);

                // PASS PRODUCTS
                $pass = false;
                if (isset($dp['price'])) {
                    if ($dp['min_quantity'] <= 0) { $pass = true; }
                    if ($dp['price'] <= 0) { $pass = true; }
                } else { $pass = true; }

                if ($pass === false) {
                    $this->_str = '';

                    $price = (int)preg_replace('/\s+/', '', $dp['price']);
                    $special = (int)preg_replace('/\s+/', '', $dp['special']);
                    if ($special !== false && $special > 0) {
                        $price = $special;
                    }

                    $seo_url = $this->getSeoUrl($product_data['product_id']);
                    $cc = $this->getCloseCat($product_data['product_id']);

                    if ($product_data['image']) {
                        $image = $base_path . 'image/' . $product_data['image'];
                    } else {
                        $image = $base_path . 'image/placeholder.png';
                    }

                    $this->_str .= "<offer id=\"{$product_data['product_id']}\">\n" .
                        "<url>{$seo_url}</url>\n" .
                        "<price>". $price ."</price>\n" .
                        "<currencyId>RUR</currencyId>\n" .
                        "<categoryId>{$cc}</categoryId>\n" .
                        "<picture>{$image}</picture>\n" .
                        ((!empty($product_data['h1'])) ? "<name>" . htmlspecialchars($product_data['h1']) . "</name>\n" : "<name>" . htmlspecialchars($product_data['name']) . "</name>\n").
                        ((!empty($product_data['manufacturer'])) ? "<vendor>" . htmlspecialchars($product_data['manufacturer']) . "</vendor>\n" : "") .
                        ((!empty('')) ? "<vendorCode>" . htmlspecialchars('') . "</vendorCode>\n" : "") .
                        ((!empty($product_data['meta_description'])) ? "<description>" . htmlspecialchars(strip_tags($product_data['meta_description'])) . "</description>\n" : "<description>Описание у товара скоро появится</description>\n")
                        ."<sales_notes>мин.сумма заказа: 1000р, мин.партия: 1шт</sales_notes>\n";

                    /* OPTIONS */
                    $options = $this->model_extension_module_super_offers->getOptions($product_data['product_id']);
                    foreach ($options as $o) {
                        $int = (strcmp($o['class'], 'size')===0) ? true : false;
                        foreach ($o['product_option_value'] as $ov) {
                            $this->_str .= "<param name=\"{$o['name']}\"" . (($int) ? " unit=\"INT\"" : "") . ">{$ov['name']}</param>\n";
                        }
                    }

                    $this->_str .= "</offer>\n";
                    fwrite($f, $this->_str);
                    unset($this->_str);

                    $pcount++;
                } else {
                    $no_price_count++;
                }

            }
        }

        $this->_str = "</offers>\n" .
            "</shop>\n" .
            "</yml_catalog>";

        fwrite($f, $this->_str);
        fclose($f);

        $json['message'][] = "Обработано {$pcount} товаров.";

        if ($no_price_count) {
            $json['message'][] = "Товаров без цены {$no_price_count}";
        }

        return $json;
    }


    private function getSeoUrl($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url
            WHERE `query` = '" . $this->db->escape('product_id=' . (int)$product_id) . "'
            AND store_id = '" . (int)$this->config->get('config_store_id') . "'
            AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

        if ($query->num_rows && $query->row['keyword']) {
            $this->load->model('tool/base');
            return $this->model_tool_base->getBase() . $query->row['keyword'];
        }

        return $this->url->link('product/product', 'product_id=' . (int)$product_id, true);
    }

    private function getBreadcrumbs($product_id)
    {
        $categories = $this->getProductCategories($product_id);
        $most_closed = $this->getMostCloseCategories($categories);

        if ($most_closed) {
            $most_closed = array_shift ($most_closed);

            $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "category c
                LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)
                WHERE c.category_id = '" . (int)$most_closed . "'
                AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

            if ($query->num_rows) {
                return ($query->row['meta_title']) ? $query->row['meta_title'] : $query->row['name'];
            }
        }

        return '';
    }

    private function getCloseCat($product_id)
    {
        $categories = $this->getProductCategories($product_id);
        $most_closed = $this->getMostCloseCategories($categories);

        if ($most_closed) {
            return array_shift ($most_closed);
        }

        return 0;
    }

    public function getProductCategories($product_id)
    {
        $categories = array();

        $query = $this->db->query("SELECT * FROM `". DB_PREFIX ."product_to_category`
            WHERE `product_id` = '" . (int)$product_id . "'");

        foreach ($query->rows as $result) {
            $categories[] = $result['category_id'];
        }

        return $categories;
    }

    private function getCategoryIdPath($category_id)
    {
        $query = $this->db->query("SELECT cp.category_id, GROUP_CONCAT(cp.path_id ORDER BY level SEPARATOR '-') AS path
            FROM `". DB_PREFIX ."category_path` cp
            WHERE cp.category_id = '". (int)$category_id ."'
            GROUP BY cp.category_id");

        if (isset($query->row['path'])) {
            return $query->row;
        }
    }

    private function getMostCloseCategories($categories)
    {
        $filtered = array();
        $pathes = array();

        foreach ($categories as $cid) {
            $pathes[] = $this->getCategoryIdPath($cid);
        }

        // SORT BY PATH LENGTH
        usort($pathes, function($a, $b) {
            return strlen($b['path']) - strlen($a['path']);
        });

        foreach ($pathes as $k1 => $orig_pd) {
            foreach ($pathes as $k2 => $test_pd) {
                if ($k1 === $k2) { continue; }

                if (strpos($orig_pd['path'], $test_pd['path']) !== false) {
                    unset($pathes[$k2]);
                }
            }
        }

        $filtered = array_map(function($pd) {
            return $pd['category_id'];
        }, $pathes);

        return ($filtered) ?: $categories;
    }

    public function getCategories($parent_id = null, $top = null)
    {
        $sql = "SELECT c.*, cd.name FROM " . DB_PREFIX . "category c
            LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)
            WHERE cd.language_id = '".(int)$this->config->get('config_language_id')."'";

        if ($parent_id !== null) {
            $sql .= " AND c.parent_id = '". (int)$parent_id ."' ";
        }
        if ($top !== null) {
            $sql .= " AND c.top = '". (int)$top ."' ";
        }

        $sql .= "ORDER BY c.category_id";

        $query = $this->db->query($sql);
        return $query->rows;
    }

    private function setTree($parent_id)
    {
        $cats = $this->getCategories($parent_id);

        if ($cats) {
            foreach ($cats as $cat) {
                $group_name = htmlspecialchars($cat['name']);
                $this->_str .= "    <category id=\"{$cat['category_id']}\" parentId=\"{$parent_id}\">{$group_name}</category>\n";
                $this->setTree($cat['category_id']);
            }
        }
    }

    private function createPath($path)
    {
        if (!is_dir(dirname($path))) {
            $d = new \import_1c\import_1c_dir;
            $d::createDir(dirname($path));
            unset($d);
        }
    }
}
