<?php
class ModelApiImport1C extends Model
{
    private $codename = 'import_1c';
    private $route = 'api/import_1c';
    private $setting_route = 'extension/module/import_1c';

    private $exchange_path;

    private $progress_id;
    private $action_id;
    private $extra;

    // STORE TABLES
    const PRODUCT_TABLE = 'product';
    const CATEGORY_TABLE = 'category';
    const MANUFACTURER_TABLE = 'manufacturer';
    const ATTRIBUTE_TABLE = 'attribute';
    const ATTRIBUTE_GROUP_TABLE = 'attribute_group';

    const IMPORT_FIELD = 'import_id';

    function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/db');
        $this->load->model('extension/module/super_offers');
        $this->load->model('api/import_1c/progress');

        $this->import_1c = new \import_1c\import_1c();
        $this->exchange_path = $this->getRootPath() . 'protected/runtime/exchange/';

        $this->files = array(
            'import*.xml',
            'offers*.xml',
        );

        $this->progress_id = (isset($this->session->data['import_1c_progress_id'])) ?
            $this->session->data['import_1c_progress_id'] : false;
        $this->action_id = (isset($this->session->data['import_1c_action_id'])) ?
            $this->session->data['import_1c_action_id'] : false;

        $this->extra = $this->model_api_import_1c_progress->getExtra($this->progress_id);
    }

    private function getRootPath()
    {
        return dirname(DIR_SYSTEM).'/';
    }

    public function actionCatalogInit()
    {
        // if (is_dir($this->exchange_path)) {
        //     \import_1c\import_1c_dir\clearDir("{$this->exchange_path}", array(
        //         '*.gitignore',
        //         '*/import_files/*.jpg',
        //         '*/import_files/*.png',
        //     ));
        // }

        $tables = array(
            self::PRODUCT_TABLE,
            self::CATEGORY_TABLE,
            self::MANUFACTURER_TABLE,
        );

        // ADD IMPORT FIELD ID TO THE TABLES
        foreach ($tables as $tn) {
            if (!$this->model_extension_pro_patch_db->isColumnExist($tn, self::IMPORT_FIELD)) {
                $this->db->query("ALTER TABLE `". DB_PREFIX . $this->db->escape($tn) . "`
                    ADD COLUMN `". $this->db->escape(self::IMPORT_FIELD) . "` VARCHAR(255) NOT NULL;");
            }
        }
    }

    public function initProgress($api_token, $data)
    {
        $progress_id = $this->model_api_import_1c_progress->isProgress($api_token);

        if (!$progress_id) {
            $this->progress_id = $this->model_api_import_1c_progress->initProgress($api_token, $data);
        } else {
            $this->progress_id = $progress_id;
        }

        $this->session->data['import_1c_progress_id'] = $this->progress_id;

        $this->session->data['import_1c_action_id'] =
            $this->model_api_import_1c_progress->saveAction($this->progress_id, $data);
    }

    public function actionCatalogFile($filename)
    {
        $json = array();
        if (empty($filename)) {
            $json['error'][] = 'Невереный filename';

            // SAVE TO LOG
            $this->model_api_import_1c_progress->parseJson(
                $this->progress_id, $json, $this->action_id);
            return $json;
        }

        try {
            $path = "{$this->exchange_path}{$filename}";
            if (!is_dir(dirname($path))) {
                \import_1c\import_1c_dir\createDir(dirname($path));
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
            $json['error'][] = 'Ошибка при сохраненнии файла';
        }

        // SAVE TO LOG
        $this->model_api_import_1c_progress->parseJson(
                $this->progress_id, $json, $this->action_id);

        return $json;
    }

    public function actionCatalogImport($filename)
    {
        $json = array();
        $json['continue'] = false;

        if (empty($filename)) {
            $json['error'][] = 'Невереный filename';
            $json['continue'] = false;
            $json['success'] = false;

            // SAVE TO LOG
            $this->model_api_import_1c_progress->parseJson(
                $this->progress_id, $json, $this->action_id);
            return $json;
        }

        $realpath = "{$this->exchange_path}{$filename}";

        if (!is_file($realpath) || !is_readable($realpath)) {
            $json['error'][] = 'Filename не существует';
            $json['continue'] = false;
            $json['success'] = false;

            // SAVE TO LOG
            $this->model_api_import_1c_progress->parseJson(
                $this->progress_id, $json, $this->action_id);
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
                    if ($this->markFileFinished($realpath) === true) {
                        $this->extra[$filetype]['finished'] = true;
                        $json['success'] = true;
                    } else {
                        $json['success'] = false;
                        $json['error'][] = 'Не удалось переименовать файл.';
                    }
                }
            } else {
                $json['success'] = true;
                $json['continue'] = true;
                // TODO: save progress and continue
            }
        }

        // SAVE TO LOG
        $this->model_api_import_1c_progress->parseJson(
                $this->progress_id, $json, $this->action_id);

        // UPDATE EXTRA
        $this->model_api_import_1c_progress->updateExtra(
                $this->progress_id, $this->extra);

        // FINISHED
        if (isset($this->extra[$this->import_1c->getImportFileType()]['finished'])
        && $this->extra[$this->import_1c->getImportFileType()]['finished'] === true
        && isset($this->extra[$this->import_1c->getOffersFileType()]['finished'])
        && $this->extra[$this->import_1c->getOffersFileType()]['finished'] === true) {
            $this->model_api_import_1c_progress->finishImport($this->progress_id);
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

        // PRODUCERS
        $this->load->model('api/import_1c/producer');
        $this->model_api_import_1c_producer->action($parsed);

        // GROUP
        $this->load->model('api/import_1c/group');
        $this->model_api_import_1c_group->action('Группа', $languages);

        // DEN
        $this->load->model('api/import_1c/group');
        $this->model_api_import_1c_group->action('Ден', $languages);

        // SOSTAV
        $this->load->model('api/import_1c/group');
        $this->model_api_import_1c_group->action('Состав', $languages);

        // OPTION
        $this->load->model('api/import_1c/option');
        $this->model_api_import_1c_option->action($parsed, $languages);

        // PRODUCTS
        $this->load->model('api/import_1c/product');
        $this->model_api_import_1c_product->action($parsed, $languages, $this->exchange_path);

        return true;
    }

    private function _offersRoutine()
    {
        // LANGUAGES
        $this->load->model('api/import_1c/language');
        $languages = $this->model_api_import_1c_language->getLanguages();

        return true;
    }

    private function markFileFinished($path)
    {
        $renamed = dirname($path).DIRECTORY_SEPARATOR.basename($path).'__FINISHED';
        return rename($path, $renamed);
    }

    public function actionCatalogDelete()
    {
        $json = array();

        $this->load->model('api/import_1c/product');
        $this->model_api_import_1c_product->deleteAllProducts();

        $json['success'] = true;

        // SAVE TO LOG
        $this->model_api_import_1c_progress->parseJson(
                $this->progress_id, $json, $this->action_id);

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
