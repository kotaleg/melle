<?php

namespace pro_csv;
require_once __DIR__ . '/vendor/autoload.php';

use Goodby\CSV\Export\Standard\ExporterConfig;
use Goodby\CSV\Import\Standard\LexerConfig;
use Goodby\CSV\Export\Standard\Exporter;
use Goodby\CSV\Export\Standard\Exception\StrictViolationException;

class pro_csv
{
    private $config;
    private $strict = true;
    private $handler;

    function __construct($type = 'EXPORT')
    {
        switch ($type) {
            case 'IMPORT':
                $this->config = new LexerConfig();
                break;

            default:
                $this->config = new ExporterConfig();
                break;
        }
    }

    public function unstrict()
    {
        $this->strict = false;
    }

    public function setDelimiter($delimiter = ",")
    {
        $this->config->setDelimiter($delimiter);
    }

    public function setToCharset($charset = null)
    {
        $this->config->setToCharset($charset);
    }

    public function setFromCharset($charset = null)
    {
        $this->config->setFromCharset($charset);
    }

    public function setColumnHeaders($headers = array())
    {
        $this->config->setColumnHeaders($headers);
    }

    public function setFileMode($fileMode)
    {
        $this->config->setFileMode($fileMode);
    }

    public function export($path = "php://output", $data = array())
    {
        $exporter = new Exporter($this->config);

        if ($this->strict === false) {
            $exporter->unstrict();
        }

        return $exporter->export($path, $data);
    }

}