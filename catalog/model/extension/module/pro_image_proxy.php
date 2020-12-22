<?php
/*
 *  location: catalog/model
 */
class ModelExtensionModulePROImageProxy extends Model
{
    private $codename = 'pro_image_proxy';
    private $route = 'extension/module/pro_image_proxy';

    private $setting = array();

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/setting');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);
    }

    public function log($message)
    {
        if (isset($this->setting['debug']) && $this->setting['debug']) {
            $this->log->write(strtoupper($this->codename)." :: {$message}");
        }
    }

    public function prepareUrl()
    {
        if (!isset($this->setting['status'])
        || (isset($this->setting['status']) && !$this->setting['status'])) {
            return;
        }

        $args = func_get_args();

        if (count($args) < 3) {
            $this->log("arguments count < 3");
            return;
        }

        $filename = $args[0];
        $width = $args[1];
        $height = $args[2];

        if (defined('JOURNAL3_ACTIVE')) {
            if (isset($args[3])) {
                $journalResizeType = $args[3];
            }
        }

        $requiredSettings = array(
            'proxy_url',
            'proxy_key',
            'proxy_secret',
        );

        foreach ($requiredSettings as $settingKey) {
            if (!isset($this->setting[$settingKey])) {
                $this->log("{$settingKey} not set");
                return;
            }
            if (empty($this->setting[$settingKey])) {
                $this->log("{$settingKey} is empty");
                return;
            }
        }

        $originalUrl = $this->getOriginalUrl($filename);

        try {

            $signedApi = new \pro_image_proxy\signed(
                $this->setting['proxy_url'],
                $this->setting['proxy_key'],
                $this->setting['proxy_secret']
            );

            $signedApi->setWidth($width);
            $signedApi->setHeight($height);
            return $signedApi->prepareSignedPath($originalUrl);

        } catch (\Exception $error) {
            $this->log($error->getMessage());
        }
    }

    private function getOriginalUrl($filename)
    {
        if ($this->request->server['HTTPS']) {
            $baseUrl = $this->config->get('config_ssl') ? $this->config->get('config_ssl') : HTTPS_CATALOG;
        } else {
            $baseUrl = $this->config->get('config_url') ? $this->config->get('config_url') : HTTP_CATALOG;
        }

        return $baseUrl . 'image/' . $filename;
    }
}
