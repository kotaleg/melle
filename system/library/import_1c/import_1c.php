<?php

namespace import_1c;
require_once __DIR__ . '/vendor/autoload.php';

use Sabre\Xml\Service;
use Sabre\Xml\Reader;
use Sabre\Xml\Deserializer;
use Sabre\Xml\XmlDeserializable;
use Lead\Dir\Dir;

use \import_1c\map\import_file_map;
use \import_1c\map\offers_file_map;

class import_1c
{
    private $service;
    private $handle;
    private $namespace = 'urn:1C.ru:commerceml_2';

    const IMPORT_FILE = 'import';
    const OFFERS_FILE = 'offers';

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

    public function getFiles()
    {
        return array(
            self::IMPORT_FILE,
            self::OFFERS_FILE,
        );
    }

    private function mapXml($path)
    {
        $basename = basename($path);
        $basename = str_replace('.xml', '', $basename);
        preg_match('/(?<name>[a-z]+)/', $basename, $matches);

        if (isset($matches['name'])) {
            switch ($matches['name']) {
                case self::IMPORT_FILE:
                    $this->service = import_file_map::mapXml($this->service, $this->namespace);
                    break;
                case self::OFFERS_FILE:
                    $this->service = offers_file_map::mapXml($this->service, $this->namespace);
                    break;
            }
        }
    }

    public function parse()
    {
        return $this->service->expect("{{$this->namespace}}КоммерческаяИнформация", $this->handle);
    }

    public function createDir($path)
    {
        return Dir::make($path,
            array(
                'mode'      => 0755,
                'recursive' => true,
            )
        );
    }

    public function clearDir($path, $exclude = array())
    {
        return Dir::remove($path,
            array(
                'followSymlinks' => false,
                'recursive'      => true,
                'exclude'        => $exclude,
            )
        );
    }
}