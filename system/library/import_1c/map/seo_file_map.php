<?php

namespace import_1c\map;

use Sabre\Xml\Service;
use Sabre\Xml\Reader;
use Sabre\Xml\Deserializer;
use Sabre\Xml\XmlDeserializable;

use import_1c\helper;

class seo_file_map
{
    private static $namespace;

    public static function mapXml(Service $service, $namespace)
    {
        self::$namespace = $namespace;


        return $service;
    }


}