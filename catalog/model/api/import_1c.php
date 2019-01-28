<?php
class ModelApiImport1C extends Model
{
    private $codename = 'import_1c';
    private $route = 'api/import_1c';
    private $setting_route = 'extension/module/import_1c';

    private $exchange_path;

    private $extra;

    // STORE TABLES
    const PRODUCT_TABLE = 'product';
    const CATEGORY_TABLE = 'category';
    const MANUFACTURER_TABLE = 'manufacturer';
    const ATTRIBUTE_TABLE = 'attribute';
    const ATTRIBUTE_GROUP_TABLE = 'attribute_group';
    const OPTION_TABLE = 'option';
    const OPTION_VALUE_TABLE = 'option_value';

    const IMPORT_FIELD = 'import_id';

    function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/db');
        $this->load->model('api/import_1c/progress');

        $this->import_1c = new \import_1c\import_1c();
        $this->exchange_path = $this->getRootPath() . 'protected/runtime/exchange/';

        $this->files = array(
            'import*.xml',
            'offers*.xml',
        );

        $this->extra = $this->model_api_import_1c_progress->getExtra();
    }

    private function getRootPath()
    {
        return dirname(DIR_SYSTEM).'/';
    }

    public function actionCatalogInit()
    {
        $json = array();

        // REMOVE OLD ONES
        foreach (glob("{$this->exchange_path}*{__OLD*,__FINISHED*}", GLOB_BRACE) as $file) {
            if (is_file($file)) {
                @unlink($file);
                $json['message'][] = 'Файл `'.basename($file).'` удален';
            }
        }

        // MARK AS OLD (PREVIOUS FILES)
        foreach (glob("{$this->exchange_path}*.xml") as $file) {
            if (is_file($file)) {
                $this->renameFile($file, '__OLD');
                $json['message'][] = 'Файл `'.basename($file).'` помечен как старый.';
            }
        }

        $tables = array(
            self::PRODUCT_TABLE,
            self::CATEGORY_TABLE,
            self::MANUFACTURER_TABLE,
            self::OPTION_TABLE,
            self::OPTION_VALUE_TABLE,
        );

        // ADD IMPORT FIELD ID TO THE TABLES
        foreach ($tables as $tn) {
            if (!$this->model_extension_pro_patch_db->isColumnExist($tn, self::IMPORT_FIELD)) {
                $this->db->query("ALTER TABLE `". DB_PREFIX . $this->db->escape($tn) . "`
                    ADD COLUMN `". $this->db->escape(self::IMPORT_FIELD) . "` VARCHAR(255) NOT NULL;");
            }
        }

        // CLEAR ELEMENTS WITHOUD DESCRIPTION
        $this->load->model('api/import_1c/category');
        $this->model_api_import_1c_category->clearCategoriesWithoutDescription();

        $json['success'] = true;

        return $json;
    }

    public function actionCatalogFile($filename)
    {
        $json = array();
        if (empty($filename)) {
            $json['error'][] = 'Невереный filename';
            return $json;
        }

        try {
            $path = "{$this->exchange_path}{$filename}";
            if (!is_dir(dirname($path))) {
                $d = new \import_1c\import_1c_dir;
                $d::createDir(dirname($path));
                unset($d);
            }

            if (is_file($path)) {
                unlink($path);
            }

            $in = fopen('php://input', 'rb');
            $out = fopen($path, 'a');
            while (!feof($in)) {
                fwrite($out, fread($in, 8192));
            }
            fclose($in);
            fclose($out);
            $json['success'] = true;
        } catch (\Exception $e) {
            $json['success'] = false;
            $json['error'][] = 'Ошибка при сохраненнии файла';
        }

        if ($json['success']) {
            if (!isset($this->extra['files_uploaded'])) {
                $this->extra['files_uploaded'] = 0;
            }

            $this->extra['files_uploaded']++;
        }

        // UPDATE EXTRA
        $this->model_api_import_1c_progress->updateExtra($this->extra);

        return $json;
    }

    public function actionCatalogImport($filename)
    {
        $json = array();
        $json['continue'] = false;

        if (empty($filename)) {
            $json['error'][] = "Невереное имя файла = `{$filename}`";
            $json['continue'] = false;
            $json['success'] = false;
            return $json;
        }

        $realpath = "{$this->exchange_path}{$filename}";

        if (!is_file($realpath) || !is_readable($realpath)) {
            $json['error'][] = "`{$filename}` не существует";
            $json['continue'] = false;
            $json['success'] = false;
            return $json;
        }

        // OPEN AND MAP THE FILE
        $this->import_1c->openFile($realpath);
        $filetype = $this->import_1c->getFileType();

        if ($filetype) {

            $this->extra[$filetype] = array(
                'filename' => $filename,
            );

            // PARSE XML TO OBJECTS
            $parsed = $this->import_1c->parse();

            switch ($filetype) {
                case $this->import_1c->getImportFileType():
                    $result = $this->_importRoutine($parsed);
                    break;

                case $this->import_1c->getOffersFileType():
                    $result = $this->_offersRoutine($parsed);
                    break;

                default:
                    $json['success'] = false;
                    $json['error'][] = 'Тип файла не распознан.';
                    break;
            }

            if (isset($result)) {
                $this->extra[$filetype]['result'] = $result;
            }

            if (isset($result) && $result === true) {
                if ($this->import_1c->done() !== true) {
                    $json['success'] = false;
                    $json['error'][] = 'Не удалось закрыть файл.';
                }

                if (!isset($json['success']) || $json['success'] != false) {
                    // if ($this->renameFile($realpath) === true) {
                        $json['success'] = true;
                        $this->extra[$filetype]['finished'] = true;
                        $json['message'][] = "Файл `{$filename}` обработан.";
                    // } else {
                    //     $json['success'] = false;
                    //     $json['error'][] = 'Не удалось переименовать файл.';
                    // }
                }
            } else {
                $json['success'] = true;
                $json['continue'] = true;
                // TODO: save progress and continue
            }
        }

        if ($json['success']) {
            if (!isset($this->extra['files_precessed'])) {
                $this->extra['files_precessed'] = 0;
            }

            $this->extra['files_precessed']++;
        }

        // UPDATE EXTRA
        $this->model_api_import_1c_progress->updateExtra($this->extra);

        // FINISHED
        $check = true;
        foreach ($this->import_1c->getFileTypes() as $type) {
            if (isset($this->extra[$type]['finished'])
            && $this->extra[$type]['finished'] === true) {
                $check = false;
            }
        }
        if ($check === true) {
            $this->model_api_import_1c_progress->finishImport();
        }

        return $json;
    }

    private function _importRoutine($parsed)
    {
        // DISABLE ALL PRODUCTS IF FULL IMPORT
        if (!$parsed->only_changes) {
            $this->disableAllProducts();
        }

        // LANGUAGES
        $this->load->model('api/import_1c/language');
        $languages = $this->model_api_import_1c_language->getLanguages();

        // MANUFACTURERS
        $this->load->model('api/import_1c/manufacturer');
        $this->model_api_import_1c_manufacturer->action($parsed);

        // DEN
        $this->load->model('api/import_1c/group');
        $this->model_api_import_1c_group->action('Ден', $languages);

        // SOSTAV
        $this->load->model('api/import_1c/group');
        $this->model_api_import_1c_group->action('Состав', $languages);

        // CATEGORY
        $this->load->model('api/import_1c/category');
        $this->model_api_import_1c_category->action($parsed, $languages);

        // PRODUCTS
        $this->load->model('api/import_1c/product');
        $this->model_api_import_1c_product->action($parsed, $languages, $this->exchange_path);

        return true;
    }

    private function _offersRoutine($parsed)
    {
        // LANGUAGES
        $this->load->model('api/import_1c/language');
        $languages = $this->model_api_import_1c_language->getLanguages();

        // OPTIONS
        $this->load->model('api/import_1c/option');
        $this->model_api_import_1c_option->action($parsed, $languages);

        // OFFERS
        $this->load->model('api/import_1c/offer');
        $this->model_api_import_1c_offer->action($parsed, $languages);

        return true;
    }

    private function renameFile($path, $postfix = '__FINISHED')
    {
        $progress_id = $this->model_api_import_1c_progress->getProgressId();

        $renamed = dirname($path).DIRECTORY_SEPARATOR.basename($path).$postfix.$progress_id;
        return rename($path, $renamed);
    }

    public function actionCatalogDelete()
    {
        $json = array();

        $this->load->model('api/import_1c/product');
        $this->model_api_import_1c_product->deleteAllProducts();

        $json['success'] = true;

        return $json;
    }

    private function disableAllProducts()
    {
        $this->db->query("UPDATE `". DB_PREFIX ."product`
            SET `status` = '".(bool)false."'");
    }

    private function disableAllCategories()
    {
        $this->db->query("UPDATE `". DB_PREFIX ."category`
            SET `status` = '".(bool)false."'");
    }
}
