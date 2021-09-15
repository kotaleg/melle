<?php
/*
 *  location: catalog/model
 */
class ModelExtensionModuleYandexMarket extends Model
{
    private $codename = 'yandex_market';
    private $route = 'extension/module/yandex_market';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/setting');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/db');
        $this->load->model('extension/pro_patch/url');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);
    }

    private function log($message)
    {
        if (isset($this->setting['debug']) && $this->setting['debug']) {
            $this->log->write(strtoupper($this->codename)." :: {$message}");
        }
    }

    public function queuePriceUpdate()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.melle.online/api/v1/yandex/market/update-price',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS =>"{
                \"campaignId\": \"{$this->setting['campaign_id']}\"
            }
            ",
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/json;charset=utf-8',
                "Authorization: {$this->setting['api_token']}"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $this->log($response);
    }
}
