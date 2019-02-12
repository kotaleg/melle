<?php

namespace import_1c;
require_once __DIR__ . '/vendor/autoload.php';

use Sabre\Xml\Service;
use Sabre\Xml\Reader;
use Sabre\Xml\Deserializer;
use Sabre\Xml\XmlDeserializable;

use \import_1c\map\import_file_map;
use \import_1c\map\offers_file_map;
use \import_1c\map\seo_file_map;

class import_1c
{
    private $service;
    private $handle;
    private $filetype;
    private $namespace = 'urn:1C.ru:commerceml_2';

    const IMPORT_FILE = 'import';
    const OFFERS_FILE = 'offers';
    const SEO_FILE = 'seo';

    function __construct()
    {
        $this->service = new Service();
    }

    public function openFile($path)
    {
        if (is_readable($path)) {
            $this->handle = fopen($path, "r");
            $this->mapXml($path);
        } else {
            throw new \Exception("File {$path} is not readable", 1);
        }
    }

    public function getFileTypes()
    {
        return array(
            self::IMPORT_FILE,
            self::OFFERS_FILE,
            self::SEO_FILE,
        );
    }

    public function getImportFileType()
    {
        return self::IMPORT_FILE;
    }

    public function getOffersFileType()
    {
        return self::OFFERS_FILE;
    }

    public function getSeoFileType()
    {
        return self::SEO_FILE;
    }

    private function mapXml($path)
    {
        $basename = basename($path);
        $basename = str_replace('.xml', '', $basename);
        preg_match('/(?<name>[a-z]+)/', $basename, $matches);

        if (isset($matches['name'])) {
            switch ($matches['name']) {
                case self::IMPORT_FILE:
                    $this->filetype = self::IMPORT_FILE;
                    $this->service = import_file_map::mapXml($this->service, $this->namespace);
                    break;
                case self::OFFERS_FILE:
                    $this->filetype = self::OFFERS_FILE;
                    $this->service = offers_file_map::mapXml($this->service, $this->namespace);
                    break;
                case self::SEO_FILE:
                    $this->filetype = self::SEO_FILE;
                    $this->service = seo_file_map::mapXml($this->service, $this->namespace);
                    break;
            }
        }
    }

    public function getFileType()
    {
        return $this->filetype;
    }

    public function parse()
    {
        return $this->service->expect("{{$this->namespace}}КоммерческаяИнформация", $this->handle);
    }

    public function done()
    {
        $this->service = null;
        return fclose($this->handle);
    }
}