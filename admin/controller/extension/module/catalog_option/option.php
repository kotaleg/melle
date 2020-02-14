<?php

class ControllerExtensionModuleCatalogOptionOption extends Controller
{
    private $codename = 'catalog_option';
    private $route = 'extension/module/catalog_option';

    private $localCodename = 'option';
    private $localRoute;

    private $catalogOptionRoute = 'catalog/option';

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->localRoute = "{$this->route}/{$this->localCodename}";

        $this->load->language($this->route);
        $this->load->language($this->catalogOptionRoute);
        $this->load->language($this->localRoute);

        $this->load->model($this->route);
        $this->load->model($this->localRoute);
        $this->load->model('extension/pro_patch/url');
        $this->load->model('extension/pro_patch/load');
        $this->load->model('extension/pro_patch/json');
        $this->load->model('extension/pro_patch/user');
        $this->load->model('extension/pro_patch/setting');
        $this->load->model('extension/pro_patch/language');
        $this->load->model('extension/pro_patch/permission');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting($this->codename);

        $this->extension_model = $this->{'model_'.str_replace("/", "_", $this->route)};
        $this->local_model = $this->{'model_'.str_replace("/", "_", $this->localRoute)};
    }

    public function index()
    {
        // STATE
        $data['codename'] = $this->codename;
        $data['state'] = json_encode($this->getState());

        $data['pro_scripts'] = $this->extension_model->getScriptFiles();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        return $this->model_extension_pro_patch_load->view("{$this->route}_list", $data);
    }

    public function getState()
    {
        $state = $this->model_extension_pro_patch_language->loadStrings(array(
            'text_list',
            'text_default',
            'text_close',
            'text_cancel',
            'text_remove_catalog_option',
            'text_remove_catalog_option_value',
            'text_no_results',
            'text_option',
            'text_value',
            
            'entry_name',
            'entry_type',
            'entry_sort_order',

            'button_edit',
            'button_delete',
            'button_add',
            'button_add_option_value',

            'help_remove_catalog_option',
            'help_remove_catalog_option_value',
        ));

        // HEADING
        $state['heading_title'] = $this->language->get('heading_title');

        // BREADCRUMB
        $state['breadcrumbs'] = array();
        $state['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->model_extension_pro_patch_url->ajax('common/dashboard'),
        );

        $state['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->model_extension_pro_patch_url->ajax($this->catalogOptionRoute),
        );

        // VARIABLE
        $state['id'] = $this->codename;
        $state['route'] = $this->catalogOptionRoute;
        $state['version'] = $this->extension['version'];
        $state['token'] = $this->model_extension_pro_patch_user->getUrlToken();

        // ACTIONS
        $state['getColumns'] = $this->model_extension_pro_patch_url
            ->ajax("{$this->localRoute}/getColumns");
        $state['getCatalogOptions'] = $this->model_extension_pro_patch_url
            ->ajax("{$this->localRoute}/getCatalogOptions");
        $state['getCatalogOptionData'] = $this->model_extension_pro_patch_url
            ->ajax("{$this->localRoute}/getCatalogOptionData");
        $state['saveCatalogOption'] = $this->model_extension_pro_patch_url
            ->ajax("{$this->localRoute}/saveCatalogOption");
        $state['deleteCatalogOption'] = $this->model_extension_pro_patch_url
            ->ajax("{$this->localRoute}/deleteCatalogOption");

        $state['getOptionValueColumns'] = $this->model_extension_pro_patch_url
            ->ajax("{$this->localRoute}/getOptionValueColumns");
        $state['getCatalogOptionValues'] = $this->model_extension_pro_patch_url
            ->ajax("{$this->localRoute}/getCatalogOptionValues");
        $state['updateCatalogOptionValueField'] = $this->model_extension_pro_patch_url
            ->ajax("{$this->localRoute}/updateCatalogOptionValueField");
        $state['updateCatalogOptionValueName'] = $this->model_extension_pro_patch_url
            ->ajax("{$this->localRoute}/updateCatalogOptionValueName");
        $state['deleteCatalogOptionValue'] = $this->model_extension_pro_patch_url
            ->ajax("{$this->localRoute}/deleteCatalogOptionValue");
        $state['canWeAddOptionValue'] = $this->model_extension_pro_patch_url
            ->ajax("{$this->localRoute}/canWeAddOptionValue");
        $state['addCatalogOptionValue'] = $this->model_extension_pro_patch_url
            ->ajax("{$this->localRoute}/addCatalogOptionValue");
        
        $state['defaultImageThumb'] = $this->local_model->getDefaultImageThumb();
        $state['defaultOptionValueName'] = $this->local_model->getDefaultOptionValueName();

        // SET STATE
        return $state;
    }

    public function getColumns()
    {
        $json = array();

        $json[] = array(
            'label' => 'column_name',
            'field' => 'name',
        );
        $json[] = array(
            'label' => 'column_sort_order',
            'field' => 'sortOrder',
            'sortable' => false,
        );
        $json[] = array(
            'label' => 'column_action',
            'field' => 'action',
            'sortable' => false,
        );

        foreach ($json as $key => $value) {
            $value['label'] = $this->language->get($value['label']);
            $json[$key] = $value;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function getCatalogOptions()
    {
        $parsed = $this->model_extension_pro_patch_json
            ->parseJson(file_get_contents('php://input'));

        $page = (isset($parsed['params']['page'])) ? (int)$parsed['params']['page'] : 1;
        $perPage = (isset($parsed['params']['perPage'])) ? (int)$parsed['params']['perPage']: 10;

        $filter = (isset($parsed['params']['columnFilters']['path'])) ? 
            $parsed['params']['columnFilters']['path'] : '';
        $sortData = (isset($parsed['params']['sort'])) ? $parsed['params']['sort'] : false;

        $filterData = array(
            'perPage'   => $perPage,
            'filter'    => $filter,
            'sortData'  => $sortData,
        );

        $totalData = $this->getCatalogOptionsTotal($filterData);
        $page = ($page > $totalData['pages']) ? $totalData['pages'] : $page;
        $page = ($page <= 0) ? 1 : $page;

        $filterData['start'] = ($page - 1) * $perPage;
        $filterData['limit'] = $perPage;

        $json['rows'] = $this->local_model->prepareComplexCatalogOptions($filterData);
        $json['total'] = (int) $totalData['total'];

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function getCatalogOptionsTotal($filterData)
    {
        $result = array(
            'total' => 0,
            'pages' => 0
        );

        $total = $this->local_model->getComplexCatalogOptionsTotal($filterData);

        if ($total) {
            $result['total'] = $total;
            $result['pages'] = ceil($total / $filterData['perPage']);
        }

        return $result;
    }

    public function getCatalogOptionData()
    {
        $parsed = $this->model_extension_pro_patch_json
            ->parseJson(file_get_contents('php://input'));

        if (isset($parsed['optionId'])) {

            if (empty($parsed['optionId'])) {
                $parsed['optionId'] = null;
            }

            $json['optionData'] = $this->local_model
                ->getCatalogOptionData($parsed['optionId']);

            $json['optionTypes'] = $this->local_model
                ->prepareOptionTypes($parsed['optionId']);

            $json['optionTypesRequireValues'] = $this->local_model
                ->getOptionTypesRequireValues();

        } else {
            $json['error'][] = $this->language->get('error_missing_data');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function saveCatalogOption()
    {
        $json = array();

        if (!$this->user->hasPermission('modify', $this->catalogOptionRoute)) {
            $json['error'][] = $this->language->get('error_catalog_option_modify_permission');
        }

        if (!isset($json['error'])) {
            $parsed = $this->model_extension_pro_patch_json
                ->parseJson(file_get_contents('php://input'));

            if (isset($parsed['optionData'])) {
                $json = array_merge($json, $this->local_model
                    ->saveCatalogOption($parsed['optionData']));
            } else {
                $json['error'][] = $this->language->get('error_missing_data');
            }

        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function deleteCatalogOption()
    {
        $json = array();

        if (!$this->user->hasPermission('modify', $this->catalogOptionRoute)) {
            $json['error'][] = $this->language->get('error_catalog_option_modify_permission');
        }

        if (!isset($json['error'])) {
            $parsed = $this->model_extension_pro_patch_json
                ->parseJson(file_get_contents('php://input'));

            if (isset($parsed['optionId'])) {
                $this->local_model->deleteCatalogOption($parsed['optionId']);
                $json['success'][] = sprintf($this->language->get('success_catalog_option_removed'),
                    $parsed['optionId']);
            } else {
                $json['error'][] = $this->language->get('error_missing_data');
            }

        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function getOptionValueColumns()
    {
        $json = array();

        $json[] = array(
            'label' => 'entry_option_value',
            'field' => 'name',
        );
        $json[] = array(
            'label' => 'entry_image',
            'field' => 'image',
            'sortable' => false,
        );
        $json[] = array(
            'label' => 'entry_sort_order',
            'field' => 'sortOrder',
        );
        $json[] = array(
            'label' => 'column_action',
            'field' => 'action',
            'sortable' => false,
        );

        foreach ($json as $key => $value) {
            $value['label'] = $this->language->get($value['label']);
            $json[$key] = $value;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function getCatalogOptionValues()
    {
        $json = array();
        $parsed = $this->model_extension_pro_patch_json
            ->parseJson(file_get_contents('php://input'));

        if (isset($parsed['optionId'])) {
            $page = (isset($parsed['params']['page'])) ? (int)$parsed['params']['page'] : 1;
            $perPage = (isset($parsed['params']['perPage'])) ? (int)$parsed['params']['perPage']: 10;

            $filter = (isset($parsed['params']['columnFilters']['path'])) ? 
                $parsed['params']['columnFilters']['path'] : '';
            $sortData = (isset($parsed['params']['sort'])) ? $parsed['params']['sort'] : false;

            $searchTerm = (isset($parsed['params']['searchTerm'])) ? $parsed['params']['searchTerm'] : false;

            $filterData = array(
                'optionId'  => $parsed['optionId'],
                'perPage'   => $perPage,
                'filter'    => $filter,
                'sortData'  => $sortData,
                'searchTerm' => $searchTerm,
            );

            $totalData = $this->getCatalogOptionValuesTotal($filterData);
            $page = ($page > $totalData['pages']) ? $totalData['pages'] : $page;
            $page = ($page <= 0) ? 1 : $page;

            $filterData['start'] = ($page - 1) * $perPage;
            $filterData['limit'] = $perPage;

            $json['rows'] = $this->local_model->prepareComplexCatalogOptionValues($filterData);
            $json['total'] = (int) $totalData['total'];
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function getCatalogOptionValuesTotal($filterData)
    {
        $result = array(
            'total' => 0,
            'pages' => 0
        );

        $total = $this->local_model->getComplexCatalogOptionValuesTotal($filterData);

        if ($total) {
            $result['total'] = $total;
            $result['pages'] = ceil($total / $filterData['perPage']);
        }

        return $result;
    }

    public function updateCatalogOptionValueField()
    {
        $json = array();

        if (!$this->user->hasPermission('modify', $this->catalogOptionRoute)) {
            $json['error'][] = $this->language->get('error_catalog_option_modify_permission');
        }

        if (!isset($json['error'])) {
            $parsed = $this->model_extension_pro_patch_json
                ->parseJson(file_get_contents('php://input'));

            if (isset($parsed['content']) && isset($parsed['name']) 
            && isset($parsed['optionValueId']) && isset($parsed['optionId'])) {
                
               $this->local_model
                    ->updateCatalogOptionValueField(
                        $parsed['optionId'], 
                        $parsed['optionValueId'], 
                        $parsed['name'], 
                        $parsed['content']
                    );
                $json['success'][] = sprintf($this->language->get('success_catalog_option_value_updated'),
                    $parsed['optionValueId']);
                $json['updated'] = true;

            } else {
                $json['error'][] = $this->language->get('error_missing_data');
            }

        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function updateCatalogOptionValueName()
    {
        $json = array();

        if (!$this->user->hasPermission('modify', $this->catalogOptionRoute)) {
            $json['error'][] = $this->language->get('error_catalog_option_modify_permission');
        }

        if (!isset($json['error'])) {
            $parsed = $this->model_extension_pro_patch_json
                ->parseJson(file_get_contents('php://input'));

            if (isset($parsed['content']) && isset($parsed['languageId']) 
            && isset($parsed['optionValueId']) && isset($parsed['optionId'])) {

                if ((utf8_strlen($parsed['content']) < 1) || (utf8_strlen($parsed['content']) > 128)) {
                    $json['error'][] = $this->language->get('error_name');
                }

                if (!isset($json['error'])) {
                    $this->local_model
                        ->updateCatalogOptionValueName(
                            $parsed['optionId'], 
                            $parsed['optionValueId'], 
                            $parsed['languageId'], 
                            $parsed['content']
                        );
                    $json['success'][] = sprintf($this->language->get('success_catalog_option_value_updated'),
                        $parsed['optionValueId']);
                    $json['updated'] = true;
                }

            } else {
                $json['error'][] = $this->language->get('error_missing_data');
            }

        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function deleteCatalogOptionValue()
    {
        $json = array();

        if (!$this->user->hasPermission('modify', $this->catalogOptionRoute)) {
            $json['error'][] = $this->language->get('error_catalog_option_modify_permission');
        }

        if (!isset($json['error'])) {
            $parsed = $this->model_extension_pro_patch_json
                ->parseJson(file_get_contents('php://input'));

            if (isset($parsed['optionId'])) {
                $this->local_model->deleteCatalogOptionValue($parsed['optionId'], $parsed['optionValueId']);
                $json['success'][] = sprintf($this->language->get('success_catalog_option_value_removed'),
                    $parsed['optionValueId']);
            } else {
                $json['error'][] = $this->language->get('error_missing_data');
            }

        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function canWeAddOptionValue()
    {
        $json['weCan'] = false;

        if (!$this->user->hasPermission('modify', $this->catalogOptionRoute)) {
            $json['error'][] = $this->language->get('error_catalog_option_modify_permission');
        }

        if (!isset($json['error'])) {
            $parsed = $this->model_extension_pro_patch_json
                ->parseJson(file_get_contents('php://input'));

            if (isset($parsed['optionId'])) {
                $optionData = $this->local_model->getCatalogOptionById($parsed['optionId']);
                if (isset($optionData['option_id'])) {
                    $json['weCan'] = true;
                } else {
                    $json['info'][] = $this->language->get('info_save_option_before_option_values');
                }
            } else {
                $json['info'][] = $this->language->get('info_save_option_before_option_values');
            }

        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function addCatalogOptionValue()
    {
        $json = array();

        if (!$this->user->hasPermission('modify', $this->catalogOptionRoute)) {
            $json['error'][] = $this->language->get('error_catalog_option_modify_permission');
        }

        if (!isset($json['error'])) {
            $parsed = $this->model_extension_pro_patch_json
                ->parseJson(file_get_contents('php://input'));

            if (isset($parsed['optionId']) && isset($parsed['data'])) {
                $json = array_merge($json, $this->local_model
                    ->saveCatalogOptionValue($parsed['optionId'], $parsed['data']));
            } else {
                $json['error'][] = $this->language->get('error_missing_data');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
}

