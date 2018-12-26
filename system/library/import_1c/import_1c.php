<?php

namespace import_1c;
require_once __DIR__ . '/vendor/autoload.php';

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

class import_1c
{
    private $service;
    private $handle;
    private $namespace = 'urn:1C.ru:commerceml_2';

    function __construct()
    {
        $this->service = new Service();
    }

    public function openFile($path)
    {
        if (is_readable($path)) {
            $this->handle = fopen($path, "r");
        } else {
            throw new \Exception("File {$path} is not readable", 1);
        }
    }

    public function mapXml()
    {
        $this->service->elementMap = array(
            "{{$this->namespace}}КоммерческаяИнформация" => function(Reader $reader) {
                $info = new info();
                $children = $reader->parseInnerTree();
                foreach($children as $child) {
                    if ($child['value'] instanceof catalog) {
                        $info->catalog = $child['value'];
                    }
                    if ($child['value'] instanceof classificator) {
                        $info->classificator = $child['value'];
                    }
                }
                return $info;
            },
            "{{$this->namespace}}Классификатор" => function(Reader $reader) {
                $classificator = new classificator();
                $keyValue = Deserializer\keyValue($reader, $this->namespace);
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
            "{{$this->namespace}}Группа" => function(Reader $reader) {
                $group = new group();
                $keyValue = Deserializer\keyValue($reader, $this->namespace);
                if (isset($keyValue['Ид'])) {
                    $group->id = $keyValue['Ид'];
                }
                if (isset($keyValue['Наименование'])) {
                    $group->name = $keyValue['Наименование'];
                }
                return $group;
            },
            "{{$this->namespace}}Материал" => function(Reader $reader) {
                $material = new material();
                $keyValue = Deserializer\keyValue($reader, $this->namespace);
                if (isset($keyValue['Ид'])) {
                    $material->id = $keyValue['Ид'];
                }
                if (isset($keyValue['Наименование'])) {
                    $material->name = $keyValue['Наименование'];
                }
                return $material;
            },
            "{{$this->namespace}}Изготовитель" => function(Reader $reader) {
                $producer = new producer();
                $keyValue = Deserializer\keyValue($reader, $this->namespace);
                if (isset($keyValue['Ид'])) {
                    $producer->id = $keyValue['Ид'];
                }
                if (isset($keyValue['Наименование'])) {
                    $producer->name = $keyValue['Наименование'];
                }
                return $producer;
            },
            "{{$this->namespace}}Свойство" => function(Reader $reader) {
                $option = new option();
                $keyValue = Deserializer\keyValue($reader, $this->namespace);
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
            "{{$this->namespace}}Справочник" => function(Reader $reader) {
                $handbook = new handbook();
                $keyValue = Deserializer\keyValue($reader, $this->namespace);
                if (isset($keyValue['ИдЗначения'])) {
                    $handbook->id = $keyValue['ИдЗначения'];
                }
                if (isset($keyValue['Значение'])) {
                    $handbook->value = $keyValue['Значение'];
                }
                return $handbook;
            },

            /* CATALOG */
            "{{$this->namespace}}Каталог" => function(Reader $reader) {
                $catalog = new catalog();
                $keyValue = Deserializer\keyValue($reader, $this->namespace);
                if (isset($keyValue['Товары']) && is_array($keyValue['Товары'])) {
                    foreach ($keyValue['Товары'] as $child) {
                        if ($child['value'] instanceof product) {
                            $catalog->products[] = $child['value'];
                        }
                    }
                }
                return $catalog;
            },
            "{{$this->namespace}}Товар" => function(Reader $reader) {
                $product = new product();
                $keyValue = Deserializer\keyValue($reader, $this->namespace);
                if (isset($keyValue['Ид'])) {
                    $product->id = $keyValue['Ид'];
                }
                if (isset($keyValue['Артикул'])) {
                    $product->artikul = $keyValue['Артикул'];
                }
                if (isset($keyValue['Наименование'])) {
                    $product->name = $keyValue['Наименование'];
                }
                if (isset($keyValue['Описание'])) {
                    $product->description = $keyValue['Описание'];
                }
                if (isset($keyValue['Картинка'])) {
                    $product->picture = $keyValue['Картинка'];
                }
                if (isset($keyValue['КоличествоДен'])) {
                    $product->den = $keyValue['КоличествоДен'];
                }
                if (isset($keyValue['Группы']) && is_array($keyValue['Группы'])) {
                    // в примере только одна группа
                    foreach ($keyValue['Группы'] as $v) {
                        if (isset($v['name']) && $v['name'] == "{{$this->namespace}}Ид") {
                            $group = new group();
                            $group->id = $v['value'];
                            $product->group = $group;
                            break;
                        }
                    }
                }
                if (isset($keyValue['Изготовитель'])
                && $keyValue['Изготовитель'] instanceof producer) {
                    $product->producer = $keyValue['Изготовитель'];
                }
                if (isset($keyValue['Состав']) && is_array($keyValue['Состав'])) {
                    foreach ($keyValue['Состав'] as $child) {
                        if ($child['value'] instanceof composition) {
                            $product->compositions[] = $child['value'];
                        }
                    }
                }
                if (isset($keyValue['ЗначенияСвойств'])) {
                    foreach ($keyValue['ЗначенияСвойств'] as $child) {
                        if ($child['value'] instanceof p_option) {
                            $product->options[] = $child['value'];
                        }
                    }
                }
                if (isset($keyValue['ЗначенияРеквизитов'])) {
                    foreach ($keyValue['ЗначенияРеквизитов'] as $child) {
                        if ($child['value'] instanceof p_requisit) {
                            $product->requisits[] = $child['value'];
                        }
                    }
                }
                if (isset($keyValue['СтавкиНалогов'])) {
                    foreach ($keyValue['СтавкиНалогов'] as $child) {
                        if ($child['value'] instanceof p_tax_rate) {
                            $product->tax_rate = $child['value'];
                        }
                    }
                }
                return $product;
            },
            "{{$this->namespace}}ЗначенияСвойства" => function(Reader $reader) {
                $p_option = new p_option();
                $keyValue = Deserializer\keyValue($reader, $this->namespace);
                if (isset($keyValue['Ид'])) {
                    $p_option->id = $keyValue['Ид'];
                }
                if (isset($keyValue['Значение'])) {
                    $p_option->value = $keyValue['Значение'];
                }
                return $p_option;
            },
            "{{$this->namespace}}СтрокаСостава" => function(Reader $reader) {
                $composition = new composition();
                $keyValue = Deserializer\keyValue($reader, $this->namespace);
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
            "{{$this->namespace}}ЗначениеРеквизита" => function(Reader $reader) {
                $p_requisit = new p_requisit();
                $keyValue = Deserializer\keyValue($reader, $this->namespace);
                if (isset($keyValue['Наименование'])) {
                    $p_requisit->name = $keyValue['Наименование'];
                }
                if (isset($keyValue['Значение'])) {
                    $p_requisit->value = $keyValue['Значение'];
                }
                return $p_requisit;
            },
            "{{$this->namespace}}СтавкаНалога" => function(Reader $reader) {
                $p_tax_rate = new p_tax_rate();
                $keyValue = Deserializer\keyValue($reader, $this->namespace);
                if (isset($keyValue['Наименование'])) {
                    $p_tax_rate->name = $keyValue['Наименование'];
                }
                if (isset($keyValue['Значение'])) {
                    $p_tax_rate->value = $keyValue['Значение'];
                }
                return $p_tax_rate;
            },
        );
    }

    public function test()
    {
        $result = $this->service->expect("{{$this->namespace}}КоммерческаяИнформация", $this->handle);

        // echo "<pre>"; print_r($result->classificator); echo "</pre>";exit;
        // echo "<pre>"; print_r($result->catalog->products); echo "</pre>";exit;

        // echo "<pre>"; print_r($result); echo "</pre>";
    }
}