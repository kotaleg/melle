<?php
/*
 *  location: catalog/model
 */
class ModelExtensionModulePriceList extends Controller
{
    private $codename = 'price_list';
    private $route = 'extension/module/price_list';
    private $type = 'module';

    const PL_TABLE = 'pl_items';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language($this->route);

        $this->load->model('extension/pro_patch/url');
        $this->load->model('extension/pro_patch/db');
        $this->load->model('extension/pro_patch/setting');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);

        $this->workFolder = dirname(DIR_SYSTEM).'/'.$this->setting['path'];
    }

    public function getWorkFolderName()
    {
        return $this->setting['path'];
    }

    public function prepareFilePath($path)
    {
        return $this->workFolder . $path;
    }

    public function isActive($information_id)
    {
        return (int)$information_id === (int)$this->setting['information_id'];
    }

    public function getPriceLists()
    {
        $q = $this->db->query("SELECT *
            FROM `". DB_PREFIX . $this->db->escape(self::PL_TABLE) . "`
            WHERE `status` = '1'
            ORDER BY `sortOrder` ASC");

        return array_map(function($v) {
            if ($this->isPriceListExist($v['filePath'])) {
                return array(
                    'title' => $v['title'],
                    '_id' => $v['_id'].'aasdf',
                );
            }
        }, $q->rows);
    }

    public function isPriceListExist($path)
    {
        if (is_readable($this->prepareFilePath($path))) {
            return true;
        }
    }

    public function getPriceList($_id)
    {
        $q = $this->db->query("SELECT *
            FROM `". DB_PREFIX . $this->db->escape(self::PL_TABLE) . "`
            WHERE `status` = '1'
            AND `_id` = '" . (int)$_id . "'");

        return $q->row;
    }

    public function increaseDownloadCount($_id)
    {
        $this->db->query("UPDATE `". DB_PREFIX . self::PL_TABLE ."`
            SET `downloadCount` = `downloadCount` + 1
            WHERE `_id` = '" . (int)$_id . "'");
    }
}