<?php
class ModelApiImport1CSeo extends Model
{
    private $codename = 'product';
    private $route = 'api/import_1c/dicount';

    const PRODUCT_TABLE = 'product';
    const PRODUCT_DESCRIPTION_TABLE = 'product_description';

    function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('api/import_1c/helper');
        $this->load->model('api/import_1c/progress');
        $this->load->model('api/import_1c/product');
        $this->load->model('extension/pro_patch/db');
    }

    public function action($parsed)
    {
        $json = array();

        try {

            $count = 0;

            $allowed = array(
                'h1' => false,
                'seo_h1' => false,
                'small_description' => false,
                'meta_description' => false,
                'meta_keyword' => false,
                'meta_title' => false,
            );

            foreach ($allowed as $k => $v) {
                if ($this->model_extension_pro_patch_db->isColumnExist(
                    self::PRODUCT_DESCRIPTION_TABLE, $k)) {
                    $allowed[$k] = true;
                }
            }

            if (isset($parsed->catalog->products)) {
                foreach ($parsed->catalog->products as $product) {

                    if (!isset($product->option)) { continue; }

                    $product_id = $this->model_api_import_1c_product->getProductByImportId($product->id);
                    if (!$product_id) { continue; }

                    if (isset($product->option->title) && !empty($product->option->title)
                    && $allowed['meta_title'] === true) {
                        $this->model_api_import_1c_product->updateProductDescriptionColumn(
                            $product_id, 'meta_title', $product->option->title);
                    }

                    if (isset($product->option->h1) && !empty($product->option->h1)) {

                        if ($allowed['h1'] === true) {
                            $this->model_api_import_1c_product->updateProductDescriptionColumn(
                                $product_id, 'h1', $product->option->h1);
                        }

                        if ($allowed['seo_h1'] === true) {
                            $this->model_api_import_1c_product->updateProductDescriptionColumn(
                                $product_id, 'seo_h1', $product->option->h1);
                        }
                    }

                    if (isset($product->option->description) && !empty($product->option->description)) {

                        if ($allowed['meta_description'] === true) {
                            $this->model_api_import_1c_product->updateProductDescriptionColumn(
                                $product_id, 'meta_description', $product->option->description);
                        }

                        if ($allowed['small_description'] === true) {
                            $this->model_api_import_1c_product->updateProductDescriptionColumn(
                                $product_id, 'small_description', $product->option->description);
                        }
                    }

                    if (isset($product->option->keywords) && !empty($product->option->keywords)) {
                        if ($allowed['meta_keyword'] === true) {
                            $this->model_api_import_1c_product->updateProductDescriptionColumn(
                                $product_id, 'meta_keyword', $product->option->keywords);
                        }
                    }

                    $count++;
                }
            }


            if ($count > 0) {
                $json['message'][] = "Обновлено {$count} SEO данных";
            }

        } catch (Exception $e) {
            $json['error'][] = 'Не удалось применить SEO данные';
        }

        // SAVE TO LOG
        $this->model_api_import_1c_progress->parseJson($json);
    }

    public function parseRedirects()
    {
        $exchange_path = dirname(DIR_SYSTEM).'/' . 'protected/runtime/exchange/';
        $csv = $exchange_path . 'redirect.csv';

        if (!is_file($csv)) { return; }

        $this->load->model('api/import_1c/language');
        $languages = $this->model_api_import_1c_language->getLanguages();

        $remove = array('https://melle.online', 'http://melle.online');

        $handle = new SplFileObject($csv, "r");
        $handle->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD
            | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        while (!$handle->eof()) {
            $ex = explode(",", rtrim($handle->fgets()));

            // 301 REDIRECT
            if (isset($ex[2]) && (int)$ex[1] === 301) {
                $from = trim(str_replace($remove, '', $ex[0]));
                $to = trim(str_replace($remove, '', $ex[2]));

                // to the home page
                if (!$to) { $to = '/'; }

                foreach ($languages as $l) {
                    if (!$this->isRedirectExist($l, $from, $to)) {
                        $this->addRedirect($l, $from, $to);
                    }
                }
            }
        }

        $handle = null;
    }

    private function isRedirectExist($language, $from, $to)
    {
        $q = $this->db->query("SELECT * FROM " . DB_PREFIX . "url_redirect
            WHERE `language_id` = '" . (int)$language . "'
            AND `query` = '" . $this->db->escape($from) . "'
            AND `redirect` = '" . $this->db->escape($to) . "'");
        if ($q->num_rows) { return true; }
    }

    private function addRedirect($language, $from, $to)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "url_redirect
            SET `query` = '" . $this->db->escape($from) . "',
                `redirect` = '" . $this->db->escape($to) . "',
                `language_id` = '" . (int) $language . "'");
    }
}