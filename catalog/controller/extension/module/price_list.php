<?php
/*
 *  location: catalog/controller
 */
class ControllerExtensionModulePriceList extends Controller
{
    private $codename = 'price_list';
    private $route = 'extension/module/price_list';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language($this->route);

        $this->load->model($this->route);
        $this->load->model('extension/pro_patch/url');
        $this->load->model('extension/pro_patch/load');
        $this->load->model('extension/pro_patch/setting');

        $this->extension_model = $this->{'model_'.str_replace("/", "_", $this->route)};
    }

    public function initPriceList()
    {
        // VARIABLE
        $state['id'] = "melle_pricelist";

        $state['priceLists'] = array();
        foreach ($this->extension_model->getPriceLists() as $pl) {
            $state['priceLists'][] = array(
                'title' => $pl['title'],
                'downloadLink' => $this->model_extension_pro_patch_url->ajax("{$this->route}/download", "pl={$pl['_id']}", true),
            );
        }

        // SET STATE
        $this->document->addState($state['id'], json_encode($state));
    }

    public function download()
    {
        $json = array();

        if (isset($this->request->get['pl'])) {
            $priceList = $this->extension_model->getPriceList($this->request->get['pl']);
            if ($priceList) {

                $filePath = $this->extension_model->prepareFilePath($priceList['filePath']);
                if (is_readable($filePath)) {

                    $this->extension_model->increaseDownloadCount($this->request->get['pl']);

                    header('Content-Description: File Transfer');
                    header('Content-Disposition: attachment; filename='.basename($priceList['filePath']));
                    header('Content-Transfer-Encoding: binary');
                    readfile($filePath);
                    exit();

                }

            }
        }

        $json['error'][] = $this->language->get('error_download');

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}