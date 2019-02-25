<?php

namespace import_1c\map;

use Sabre\Xml\Service;
use Sabre\Xml\Reader;
use Sabre\Xml\Deserializer;
use Sabre\Xml\XmlDeserializable;

use import_1c\helper;
use import_1c\map\seo\info;
use import_1c\map\seo\catalog;
use import_1c\map\seo\product;
use import_1c\map\seo\option;

class seo_file_map
{
    private static $namespace;

    public static function mapXml(Service $service, $namespace)
    {
        self::$namespace = $namespace;

        $service->elementMap = array(
            '{'.self::$namespace.'}КоммерческаяИнформация' => function(Reader $reader) {
                $info = new info();
                $children = $reader->parseInnerTree();
                foreach($children as $child) {
                    if ($child['value'] instanceof catalog) {
                        if (array_key_exists('СодержитТолькоИзменения', $child['attributes'])) {
                            $info->only_changes = helper::parseBool($child['attributes']['СодержитТолькоИзменения']);
                        }
                        $info->catalog = $child['value'];
                    }
                }
                return $info;
            },

            /* CATALOG */
            '{'.self::$namespace.'}Каталог' => function(Reader $reader) {
                $catalog = new catalog();
                $keyValue = Deserializer\keyValue($reader, self::$namespace);
                if (isset($keyValue['Товары']) && is_array($keyValue['Товары'])) {
                    foreach ($keyValue['Товары'] as $child) {
                        if ($child['value'] instanceof product) {
                            $catalog->products[] = $child['value'];
                        }
                    }
                }
                return $catalog;
            },
            '{'.self::$namespace.'}Товар' => function(Reader $reader) {
                $product = new product();
                $children = $reader->parseInnerTree();

                foreach($children as $child) {
                    if (!isset($child['name'])) { continue; }

                    if ($child['name'] == '{'.self::$namespace.'}Ид') {
                        $product->id = $child['value'];
                    }
                    if ($child['name'] == '{'.self::$namespace.'}Наименование') {
                        $product->name = $child['value'];
                    }

                    if ($child['name'] == '{'.self::$namespace.'}ЗначенияСвойств') {
                        $product->option = $child['value'];
                    }
                }

                return $product;
            },
            '{'.self::$namespace.'}ЗначенияСвойств' => function(Reader $reader) {
                $option = new option();
                $keyValue = Deserializer\keyValue($reader, self::$namespace);
                if (isset($keyValue['Заголовок'])) {
                    $option->title = $keyValue['Заголовок'];
                }
                if (isset($keyValue['ЗаголовокH1'])) {
                    $option->h1 = $keyValue['ЗаголовокH1'];
                }
                if (isset($keyValue['Описание'])) {
                    $option->description = $keyValue['Описание'];
                }
                if (isset($keyValue['КлючевыеСлова'])) {
                    $option->keywords = $keyValue['КлючевыеСлова'];
                }
                return $option;
            },
        );

        return $service;
    }


}