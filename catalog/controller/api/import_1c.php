<?php
class ControllerApiImport1C extends Controller
{
    private $codename = 'import_1c';
    private $route = 'api/import_1c';
    private $setting_route = 'extension/module/import_1c';

    function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->model($this->route);
        $this->load->language('api/import_1c');

        $this->extension_model = $this->{'model_'.str_replace("/", "_", $this->route)};
    }

    public function index()
    {
        $json = array();

        if (!isset($this->session->data['api_id'])) {
            $json['error'][] = $this->language->get('error_permission');
        } else {
            $time_start = microtime(true);

            if (isset($this->request->get['type']) && isset($this->request->get['mode'])) {
                $type = $this->db->escape($this->request->get['type']);
                $mode = $this->db->escape($this->request->get['mode']);
                $process = 'action' . ucfirst($type) . ucfirst($mode);

                $filename = isset($this->request->get['filename']) ?
                    $this->db->escape(trim($this->request->get['filename'])) : null;

                // SAVE PROGRESS AND LOG ACTION
                $this->load->model('api/import_1c/progress');
                $this->model_api_import_1c_progress->_init(
                    $this->request->get['api_token'], array(
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
                    $this->log->write("{$this->route} {$process} {$e->getMessage()}");
                    $json['error'][] = $this->language->get('error_action');
                }

                // SAVE TO LOG
                $this->model_api_import_1c_progress->parseJson($json);

            } else {
                $json['error'][] = $this->language->get('error_no_data');
            }

            $time_end = microtime(true);
            $json['time'] = $time_end - $time_start;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
