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
use import_1c\map\import\groups_container;
use import_1c\map\import\group;
use import_1c\map\import\products_container;
use import_1c\map\import\product;
use import_1c\map\import\materials_container;
use import_1c\map\import\material;
use import_1c\map\import\producers_container;
use import_1c\map\import\producer;
use import_1c\map\import\options_container;
use import_1c\map\import\option;
use import_1c\map\import\option_variants_container;
use import_1c\map\import\handbook;
use import_1c\map\import\compositions_container;
use import_1c\map\import\composition;

class import_1c
{
    private $service;
    private $handle;

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
            '{urn:1C.ru:commerceml_2}КоммерческаяИнформация' => function(Reader $reader) {
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
            '{urn:1C.ru:commerceml_2}Классификатор' => function(Reader $reader) {
                $classificator = new classificator();
                $children = $reader->parseInnerTree();
                foreach($children as $child) {
                    if ($child['value'] instanceof groups_container) {
                        $classificator->groups_container = $child['value'];
                    }
                    if ($child['value'] instanceof materials_container) {
                        $classificator->materials_container = $child['value'];
                    }
                    if ($child['value'] instanceof producers_container) {
                        $classificator->producers_container = $child['value'];
                    }
                    if ($child['value'] instanceof options_container) {
                        $classificator->options_container = $child['value'];
                    }
                }
                return $classificator;
            },

            '{urn:1C.ru:commerceml_2}Группы' => function(Reader $reader) {
                $groups_container = new groups_container();
                $children = $reader->parseInnerTree();
                foreach($children as $child) {
                    if ($child['value'] instanceof group) {
                        $groups_container->groups[] = $child['value'];
                    }
                }
                return $groups_container;
            },
            '{urn:1C.ru:commerceml_2}Группа' => function(Reader $reader) {
                $group = new group();
                $keyValue = Deserializer\keyValue($reader, 'urn:1C.ru:commerceml_2');
                if (isset($keyValue['Ид'])) {
                    $group->id = $keyValue['Ид'];
                }
                if (isset($keyValue['Наименование'])) {
                    $group->name = $keyValue['Наименование'];
                }
                return $group;
            },

            '{urn:1C.ru:commerceml_2}Материалы' => function(Reader $reader) {
                $materials_container = new materials_container();
                $children = $reader->parseInnerTree();
                foreach($children as $child) {
                    if ($child['value'] instanceof material) {
                        $materials_container->materials[] = $child['value'];
                    }
                }
                return $materials_container;
            },
            '{urn:1C.ru:commerceml_2}Материал' => function(Reader $reader) {
                $material = new material();
                $keyValue = Deserializer\keyValue($reader, 'urn:1C.ru:commerceml_2');
                if (isset($keyValue['Ид'])) {
                    $material->id = $keyValue['Ид'];
                }
                if (isset($keyValue['Наименование'])) {
                    $material->name = $keyValue['Наименование'];
                }
                return $material;
            },

            '{urn:1C.ru:commerceml_2}Изготовители' => function(Reader $reader) {
                $producers_container = new producers_container();
                $children = $reader->parseInnerTree();
                foreach($children as $child) {
                    if ($child['value'] instanceof producer) {
                        $producers_container->producers[] = $child['value'];
                    }
                }
                return $producers_container;
            },
            '{urn:1C.ru:commerceml_2}Изготовитель' => function(Reader $reader) {
                $producer = new producer();
                $keyValue = Deserializer\keyValue($reader, 'urn:1C.ru:commerceml_2');
                if (isset($keyValue['Ид'])) {
                    $producer->id = $keyValue['Ид'];
                }
                if (isset($keyValue['Наименование'])) {
                    $producer->name = $keyValue['Наименование'];
                }
                return $producer;
            },

            '{urn:1C.ru:commerceml_2}Свойства' => function(Reader $reader) {
                $options_container = new options_container();
                $children = $reader->parseInnerTree();
                foreach($children as $child) {
                    if ($child['value'] instanceof option) {
                        $options_container->options[] = $child['value'];
                    }
                }
                return $options_container;
            },
            '{urn:1C.ru:commerceml_2}Свойство' => function(Reader $reader) {
                $option = new option();
                $keyValue = Deserializer\keyValue($reader, 'urn:1C.ru:commerceml_2');
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
                    if (isset($keyValue['ВариантыЗначений'])
                    && $keyValue['ВариантыЗначений'] instanceof option_variants_container) {
                        $option->option_variants_container = $keyValue['ВариантыЗначений'];
                    }
                }
                return $option;
            },
            '{urn:1C.ru:commerceml_2}ВариантыЗначений' => function(Reader $reader) {
                $option_variants_container = new option_variants_container();
                $children = $reader->parseInnerTree();
                foreach($children as $child) {
                    if ($child['value'] instanceof handbook) {
                        $option_variants_container->variants[] = $child['value'];
                    }
                }
                return $option_variants_container;
            },
            '{urn:1C.ru:commerceml_2}Справочник' => function(Reader $reader) {
                $handbook = new handbook();
                $keyValue = Deserializer\keyValue($reader, 'urn:1C.ru:commerceml_2');
                if (isset($keyValue['ИдЗначения'])) {
                    $handbook->id = $keyValue['ИдЗначения'];
                }
                if (isset($keyValue['Значение'])) {
                    $handbook->value = $keyValue['Значение'];
                }
                return $handbook;
            },

            '{urn:1C.ru:commerceml_2}Каталог' => function(Reader $reader) {
                $catalog = new catalog();
                $children = $reader->parseInnerTree();
                foreach($children as $child) {
                    if ($child['value'] instanceof products_container) {
                        $catalog->products_container = $child['value'];
                    }
                }
                return $catalog;
            },
            '{urn:1C.ru:commerceml_2}Товары' => function(Reader $reader) {
                $products_container = new products_container();
                $children = $reader->parseInnerTree();
                foreach($children as $child) {
                    if ($child['value'] instanceof product) {
                        $products_container->products[] = $child['value'];
                    }
                }
                return $products_container;
            },
            '{urn:1C.ru:commerceml_2}Товар' => function(Reader $reader) {
                $product = new product();
                $keyValue = Deserializer\keyValue($reader, 'urn:1C.ru:commerceml_2');
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
                if (isset($keyValue['Состав'])
                && $keyValue['Состав'] instanceof compositions_container) {
                    $product->compositions_container = $keyValue['Состав'];
                }
                return $product;
            },

            '{urn:1C.ru:commerceml_2}Состав' => function(Reader $reader) {
                $compositions_container = new compositions_container();
                $children = $reader->parseInnerTree();
                foreach($children as $child) {
                    if ($child['value'] instanceof composition) {
                        $compositions_container->values[] = $child['value'];
                    }
                }
                return $compositions_container;
            },
            '{urn:1C.ru:commerceml_2}СтрокаСостава' => function(Reader $reader) {
                $composition = new composition();
                $keyValue = Deserializer\keyValue($reader, 'urn:1C.ru:commerceml_2');
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
        );
    }

    public function test()
    {
        $result = $this->service->expect('{urn:1C.ru:commerceml_2}КоммерческаяИнформация', $this->handle);

        echo "<pre>"; print_r($result->catalog->products_container->products[0]); echo "</pre>";exit;

        // echo "<pre>"; print_r($result); echo "</pre>";
    }
}