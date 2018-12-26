<?php

namespace import_1c\map;

use Sabre\Xml\Service;
use Sabre\Xml\Reader;
use Sabre\Xml\Deserializer;
use Sabre\Xml\XmlDeserializable;

use import_1c\map\import\info;
use import_1c\map\import\classificator;
use import_1c\map\import\catalog;
use import_1c\map\import\group;
use import_1c\map\import\product;
use import_1c\map\import\material;
use import_1c\map\import\producer;
use import_1c\map\import\option;
use import_1c\map\import\handbook;
use import_1c\map\import\composition;
use import_1c\map\import\p_option;
use import_1c\map\import\p_requisit;
use import_1c\map\import\p_tax_rate;

class offers_file_map
{
    private static $namespace;

    public static function mapXml(Service $service, $namespace)
    {
        self::$namespace = $namespace;

        $service->elementMap = array(
        );

        return $service;
    }
}