<?php
class ControllerApiExport extends Controller
{
    private $codename = 'export';
    private $route = 'api/export';
    private $setting_route = 'extension/module/export';

    function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->language('api/export');

        $this->load->model($this->route);
        $this->load->model('extension/pro_patch/setting');

        $this->setting = $this->model_extension_pro_patch_setting->getSetting('import_1c');

        $this->extension_model = $this->{'model_'.str_replace("/", "_", $this->route)};
    }

    public function index()
    {
        $json = array();

        if (isset($this->request->get['key'])
        && strcmp($this->setting['key'], $this->request->get['key']) === 0) {
            $time_start = microtime(true);

            if (isset($this->request->get['type'])) {
                $type = $this->db->escape($this->request->get['type']);

                $mode = 'export';
                if (isset($this->request->get['mode'])) {
                    $mode = $this->db->escape($this->request->get['mode']);
                }

                $process = 'action' . ucfirst($type) . ucfirst($mode);

                $filename = isset($this->request->get['filename']) ?
                    $this->db->escape(trim($this->request->get['filename'])) : null;

                // SAVE PROGRESS AND LOG ACTION
                $this->load->model('api/import_1c/progress');
                $this->model_api_import_1c_progress->_init(
                    $this->request->get['key'], array(
                        'type'  => $type,
                        'mode'  => $mode,
                        'filename'  => $filename,
                        'extra'  => array(),
                ));

                try {
                    $result = $this->extension_model->{$process}($filename);
                    if (is_array($result)) {
                        $json = array_merge_recursive($json, $result);
                    }
                } catch (\Exception $e) {
                    $this->log->write(json_encode($e));
                    $json['error'][] = $this->language->get('error_action');
                }

                // SAVE TO LOG
                $this->model_api_import_1c_progress->parseJson($json);

            } else {
                $json['error'][] = $this->language->get('error_no_data');
            }

            $time_end = microtime(true);
            $json['time'] = $time_end - $time_start;
        } else {
            $json['error'][] = $this->language->get('error_permission');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}