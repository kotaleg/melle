<?php
/*
 *  location: catalog/model
 */
class ModelExtensionModuleExportCombinations extends Model
{
    private $codename = 'export_combinations';
    private $route = 'extension/module/export_combinations';

    private $setting = array();
    private $super_offers;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model('extension/pro_patch/setting');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/db');
        $this->load->model('extension/pro_patch/url');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);
        $this->super_offers = new \super_offers($registry);
    }

    private function log($message)
    {
        if (isset($this->setting['debug']) && $this->setting['debug']) {
            $this->log->write(strtoupper($this->codename)." :: {$message}");
        }
    }

    public function fillCombinations($combinations)
    {
        $combinationsData = array();

        foreach ($combinations as $importId) {
            $combinationData = $this->super_offers->getCombinationByImportId($importId);
            if (!$combinationData || !isset($combinationData['image'])) {
                $combinationsData[$importId] = false;
                continue;
            }
            $combinationsData[$importId] = array(
                'image' => $combinationData['image'],
            );
        }

        return $combinationsData;
    }
}
