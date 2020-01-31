<?php

namespace import_1c\map;

use Sabre\Xml\Service;
use Sabre\Xml\Reader;
use Sabre\Xml\Deserializer;
use Sabre\Xml\XmlDeserializable;

use import_1c\helper;
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

class import_file_map
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
                    if ($child['value'] instanceof classificator) {
                        $info->classificator = $child['value'];
                    }
                }
                return $info;
            },
            '{'.self::$namespace.'}Классификатор' => function(Reader $reader) {
                $classificator = new classificator();
                $keyValue = Deserializer\keyValue($reader, self::$namespace);
                if (isset($keyValue['Группы']) && is_array($keyValue['Группы'])) {
                    foreach ($keyValue['Группы'] as $child) {
                        if ($child['value'] instanceof group) {
                            $classificator->groups[] = $child['value'];
                        }
                    }
                }
                if (isset($keyValue['Материалы']) && is_array($keyValue['Материалы'])) {
                    foreach ($keyValue['Материалы'] as $child) {
                        if ($child['value'] instanceof material) {
                            $classificator->materials[] = $child['value'];
                        }
                    }
                }
                if (isset($keyValue['Изготовители']) && is_array($keyValue['Изготовители'])) {
                    foreach ($keyValue['Изготовители'] as $child) {
                        if ($child['value'] instanceof producer) {
                            $classificator->producers[] = $child['value'];
                        }
                    }
                }
                if (isset($keyValue['Свойства']) && is_array($keyValue['Свойства'])) {
                    foreach ($keyValue['Свойства'] as $child) {
                        if ($child['value'] instanceof option) {
                            $classificator->options[] = $child['value'];
                        }
                    }
                }
                return $classificator;
            },
            '{'.self::$namespace.'}Группа' => function(Reader $reader) {
                $group = new group();
                $keyValue = Deserializer\keyValue($reader, self::$namespace);
                if (isset($keyValue['Ид'])) {
                    $group->id = $keyValue['Ид'];
                }
                if (isset($keyValue['Наименование'])) {
                    $group->name = $keyValue['Наименование'];
                }
                return $group;
            },
            '{'.self::$namespace.'}Материал' => function(Reader $reader) {
                $material = new material();
                $keyValue = Deserializer\keyValue($reader, self::$namespace);
                if (isset($keyValue['Ид'])) {
                    $material->id = $keyValue['Ид'];
                }
                if (isset($keyValue['Наименование'])) {
                    $material->name = $keyValue['Наименование'];
                }
                return $material;
            },
            '{'.self::$namespace.'}Изготовитель' => function(Reader $reader) {
                $producer = new producer();
                $keyValue = Deserializer\keyValue($reader, self::$namespace);
                if (isset($keyValue['Ид'])) {
                    $producer->id = $keyValue['Ид'];
                }
                if (isset($keyValue['Наименование'])) {
                    $producer->name = $keyValue['Наименование'];
                }
                return $producer;
            },
            '{'.self::$namespace.'}Свойство' => function(Reader $reader) {
                $option = new option();
                $keyValue = Deserializer\keyValue($reader, self::$namespace);
                if (isset($keyValue['Ид'])) {
                    $option->id = $keyValue['Ид'];
                }
                if (isset($keyValue['Наименование'])) {
                    $option->name = $keyValue['Наименование'];
                }
                if (isset($keyValue['ТипЗначений'])) {
                    $option->type = $keyValue['ТипЗначений'];
                }
                if (isset($keyValue['ТипЗначений']) && $keyValue['ТипЗначений'] == 'Справочник') {
                    if (isset($keyValue['ВариантыЗначений']) && is_array($keyValue['ВариантыЗначений'])) {
                        foreach ($keyValue['ВариантыЗначений'] as $child) {
                            if ($child['value'] instanceof handbook) {
                                $option->variants[] = $child['value'];
                            }
                        }
                    }
                }
                return $option;
            },
            '{'.self::$namespace.'}Справочник' => function(Reader $reader) {
                $handbook = new handbook();
                $keyValue = Deserializer\keyValue($reader, self::$namespace);
                if (isset($keyValue['ИдЗначения'])) {
                    $handbook->id = $keyValue['ИдЗначения'];
                }
                if (isset($keyValue['Значение'])) {
                    $handbook->value = $keyValue['Значение'];
                }
                return $handbook;
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
                    if ($child['name'] == '{'.self::$namespace.'}Артикул') {
                        $product->artikul = $child['value'];
                    }
                    if ($child['name'] == '{'.self::$namespace.'}Код') {
                        $product->sku = $child['value'];
                    }
                    if ($child['name'] == '{'.self::$namespace.'}Наименование') {
                        $product->name = $child['value'];
                    }
                    if ($child['name'] == '{'.self::$namespace.'}Описание') {
                        $product->description = $child['value'];
                    }
                    if ($child['name'] == '{'.self::$namespace.'}Картинка') {
                        $product->pictures[] = $child['value'];
                    }
                    if ($child['name'] == '{'.self::$namespace.'}КоличествоДен') {
                        $product->den = $child['value'];
                    }
                    if ($child['name'] == '{'.self::$namespace.'}Группы') {
                        if (!is_array($child['value'])) { continue; }

                        // в примере только одна группа
                        foreach ($child['value'] as $v) {
                            if (isset($v['name']) && $v['name'] == '{'.self::$namespace.'}Ид') {
                                $group = new group();
                                $group->id = $v['value'];
                                $product->group = $group;
                                break;
                            }
                        }
                    }
                    if ($child['name'] == '{'.self::$namespace.'}Изготовитель') {
                        $product->producer = $child['value'];
                    }
                    if ($child['name'] == '{'.self::$namespace.'}Состав') {
                        foreach ($child['value'] as $c) {
                            if ($c['value'] instanceof composition) {
                                $product->compositions[] = $c['value'];
                            }
                        }
                    }
                    if ($child['name'] == '{'.self::$namespace.'}ЗначенияСвойств') {
                        foreach ($child['value'] as $c) {
                            if ($c['value'] instanceof p_option) {
                                $product->options[] = $c['value'];
                            }
                        }
                    }
                    if ($child['name'] == '{'.self::$namespace.'}ЗначенияРеквизитов') {
                        foreach ($child['value'] as $c) {
                            if ($c['value'] instanceof p_requisit) {
                                $product->requisits[] = $c['value'];
                            }
                        }
                    }
                    if ($child['name'] == '{'.self::$namespace.'}СтавкиНалогов') {
                        foreach ($child['value'] as $c) {
                            if ($c['value'] instanceof p_tax_rate) {
                                $product->tax_rate[] = $c['value'];
                            }
                        }
                    }
                }

                return $product;
            },
            '{'.self::$namespace.'}ЗначенияСвойства' => function(Reader $reader) {
                $p_option = new p_option();
                $keyValue = Deserializer\keyValue($reader, self::$namespace);
                if (isset($keyValue['Ид'])) {
                    $p_option->id = $keyValue['Ид'];
                }
                if (isset($keyValue['Значение'])) {
                    $p_option->value = $keyValue['Значение'];
                }
                return $p_option;
            },
            '{'.self::$namespace.'}СтрокаСостава' => function(Reader $reader) {
                $composition = new composition();
                $keyValue = Deserializer\keyValue($reader, self::$namespace);
                if (isset($keyValue['Ид'])) {
                    $composition->id = $keyValue['Ид'];
                }
                if (isset($keyValue['Наименование'])) {
                    $composition->name = $keyValue['Наименование'];
                }
                if (isset($keyValue['Процент'])) {
                    $composition->percent = $keyValue['Процент'];
                }
                return $composition;
            },
            '{'.self::$namespace.'}ЗначениеРеквизита' => function(Reader $reader) {
                $p_requisit = new p_requisit();
                $keyValue = Deserializer\keyValue($reader, self::$namespace);
                if (isset($keyValue['Наименование'])) {
                    $p_requisit->name = $keyValue['Наименование'];
                }
                if (isset($keyValue['Значение'])) {
                    $p_requisit->value = $keyValue['Значение'];
                }
                return $p_requisit;
            },
            '{'.self::$namespace.'}СтавкаНалога' => function(Reader $reader) {
                $p_tax_rate = new p_tax_rate();
                $keyValue = Deserializer\keyValue($reader, self::$namespace);
                if (isset($keyValue['Наименование'])) {
                    $p_tax_rate->name = $keyValue['Наименование'];
                }
                if (isset($keyValue['Значение'])) {
                    $p_tax_rate->value = $keyValue['Значение'];
                }
                return $p_tax_rate;
            },
        );

        return $service;
    }
}