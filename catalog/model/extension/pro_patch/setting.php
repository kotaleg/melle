<?php
/*
 *  location: admin/model
 *
 */
class ModelExtensionProPatchSetting extends Model
{
    function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('setting/setting');
    }

    public function getSetting($code, $store_id = 0)
    {
        $setting = array();
        $config_filename = $this->getConfigFilename($code);
        $store_setting = $this->model_setting_setting->getSetting($code, $store_id);

        if ($config_filename) {
            $this->config->load($config_filename);
        }

        $setting = ($this->config->get($code)) ? $this->config->get($code) : array();

        foreach ($store_setting as $k => $v) {
            $key = str_replace("{$code}_", "", $k);
            $store_setting[$key] = $v;
            unset($store_setting[$k]);
        }

        if (!empty($store_setting)) {
            $setting = array_replace_recursive($setting, $store_setting);
        }

        return $setting;
    }

    private function getConfigFilename($code)
    {
        $setting = $this->config->get("{$code}_setting");

        if (isset($setting['config'])) {
            return $setting['config'];
        }

        $full = DIR_SYSTEM . "config/{$code}.php";
        if (file_exists($full)) { return $code; }

        return false;
    }
}