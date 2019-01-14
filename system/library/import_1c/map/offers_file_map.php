<?php

namespace import_1c\map;

use Sabre\Xml\Service;
use Sabre\Xml\Reader;
use Sabre\Xml\Deserializer;
use Sabre\Xml\XmlDeserializable;

use import_1c\helper;
use import_1c\map\offers\info;
use import_1c\map\offers\classificator;
use import_1c\map\offers\offers_pack;
use import_1c\map\offers\option;
use import_1c\map\offers\handbook;
use import_1c\map\offers\price_type;
use import_1c\map\offers\tax;
use import_1c\map\offers\stock;
use import_1c\map\offers\offer;
use import_1c\map\offers\price;
use import_1c\map\offers\p_option;
use import_1c\map\offers\specification;


class offers_file_map
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
                    if ($child['value'] instanceof offers_pack) {
                        if (array_key_exists('СодержитТолькоИзменения', $child['attributes'])) {
                            $info->only_changes = helper::parseBool($child['attributes']['СодержитТолькоИзменения']);
                        }
                        $info->offers_pack = $child['value'];
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
                if (isset($keyValue['Свойства']) && is_array($keyValue['Свойства'])) {
                    foreach ($keyValue['Свойства'] as $child) {
                        if ($child['value'] instanceof option) {
                            $classificator->options[] = $child['value'];
                        }
                    }
                }
                return $classificator;
            },
            '{'.self::$namespace.'}Свойство' => function(Reader $reader) {
                $option = new option();
                $keyValue = Deserializer\keyValue($reader, self::$namespace);
                if (isset($keyValue['Ид'])) {
                    $option->id = $keyValue['Ид'];
                }
                if (isset($keyValue['ДляПредложений'])) {
                    $option->for_offers = helper::parseBool($keyValue['ДляПредложений']);
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

            /* OFFERS PACK */
            '{'.self::$namespace.'}ПакетПредложений' => function(Reader $reader) {
                $offers_pack = new offers_pack();
                $keyValue = Deserializer\keyValue($reader, self::$namespace);
                if (isset($keyValue['ТипыЦен']) && is_array($keyValue['ТипыЦен'])) {
                    foreach ($keyValue['ТипыЦен'] as $child) {
                        if ($child['value'] instanceof price_type) {
                            $offers_pack->price_type = $child['value'];
                            break;
                        }
                    }
                }
                if (isset($keyValue['Склады']) && is_array($keyValue['Склады'])) {
                    foreach ($keyValue['Склады'] as $child) {
                        if ($child['value'] instanceof stock) {
                            $offers_pack->stock = $child['value'];
                            break;
                        }
                    }
                }
                if (isset($keyValue['Предложения']) && is_array($keyValue['Предложения'])) {
                    foreach ($keyValue['Предложения'] as $child) {
                        if ($child['value'] instanceof offer) {
                            $offers_pack->offers[] = $child['value'];
                        }
                    }
                }
                return $offers_pack;
            },
            '{'.self::$namespace.'}ТипЦены' => function(Reader $reader) {
                $price_type = new price_type();
                $keyValue = Deserializer\keyValue($reader, self::$namespace);
                if (isset($keyValue['Ид'])) {
                    $price_type->id = $keyValue['Ид'];
                }
                if (isset($keyValue['Наименование'])) {
                    $price_type->name = $keyValue['Наименование'];
                }
                if (isset($keyValue['Валюта'])) {
                    $price_type->currency = $keyValue['Валюта'];
                }
                if (isset($keyValue['Налог'])
                && $keyValue['Налог'] instanceof tax) {
                    $price_type->tax = $keyValue['Налог'];
                }
                return $price_type;
            },
            '{'.self::$namespace.'}Налог' => function(Reader $reader) {
                $tax = new tax();
                $keyValue = Deserializer\keyValue($reader, self::$namespace);
                if (isset($keyValue['Наименование'])) {
                    $tax->name = $keyValue['Наименование'];
                }
                if (isset($keyValue['Акциз'])) {
                    $tax->excise = helper::parseBool($keyValue['Акциз']);
                }
                if (isset($keyValue['УчтеноВСумме'])) {
                    $tax->included_in_sum = helper::parseBool($keyValue['УчтеноВСумме']);
                }
                return $tax;
            },
            '{'.self::$namespace.'}Склад' => function(Reader $reader) {
                $stock = new stock();
                $keyValue = Deserializer\keyValue($reader, self::$namespace);
                if (isset($keyValue['Ид'])) {
                    $stock->id = $keyValue['Ид'];
                }
                if (isset($keyValue['Наименование'])) {
                    $stock->name = $keyValue['Наименование'];
                }
                return $stock;
            },
            '{'.self::$namespace.'}Предложение' => function(Reader $reader) {
                $offer = new offer();
                $keyValue = Deserializer\keyValue($reader, self::$namespace);
                if (isset($keyValue['Ид'])) {
                    $offer->id = $keyValue['Ид'];
                }
                if (isset($keyValue['Артикул'])) {
                    $offer->artikul = $keyValue['Артикул'];
                }
                if (isset($keyValue['Наименование'])) {
                    $offer->name = $keyValue['Наименование'];
                }
                if (isset($keyValue['Количество'])) {
                    $offer->quantity = $keyValue['Количество'];
                }
                if (isset($keyValue['ЗначенияСвойств'])) {
                    foreach ($keyValue['ЗначенияСвойств'] as $child) {
                        if ($child['value'] instanceof p_option) {
                            $offer->options[] = $child['value'];
                        }
                    }
                }
                if (isset($keyValue['ХарактеристикиТовара'])) {
                    foreach ($keyValue['ХарактеристикиТовара'] as $child) {
                        if ($child['value'] instanceof specification) {
                            $offer->specifications[] = $child['value'];
                        }
                    }
                }
                if (isset($keyValue['Цены'])) {
                    foreach ($keyValue['Цены'] as $child) {
                        if ($child['value'] instanceof price) {
                            $offer->price = $child['value'];
                            break;
                        }
                    }
                }
                return $offer;
            },
            '{'.self::$namespace.'}ЗначенияСвойства' => function(Reader $reader) {
                $p_option = new p_option();
                $keyValue = Deserializer\keyValue($reader, self::$namespace);
                if (isset($keyValue['Ид'])) {
                    $p_option->id = $keyValue['Ид'];
                }
                if (isset($keyValue['Наименование'])) {
                    $p_option->name = $keyValue['Наименование'];
                }
                if (isset($keyValue['Значение'])) {
                    $p_option->value = $keyValue['Значение'];
                }
                return $p_option;
            },
            '{'.self::$namespace.'}ХарактеристикаТовара' => function(Reader $reader) {
                $specification = new specification();
                $keyValue = Deserializer\keyValue($reader, self::$namespace);
                if (isset($keyValue['Ид'])) {
                    $specification->id = $keyValue['Ид'];
                }
                if (isset($keyValue['Наименование'])) {
                    $specification->name = $keyValue['Наименование'];
                }
                if (isset($keyValue['Значение'])) {
                    $specification->value = $keyValue['Значение'];
                }
                return $specification;
            },
            '{'.self::$namespace.'}Цена' => function(Reader $reader) {
                $price = new price();
                $keyValue = Deserializer\keyValue($reader, self::$namespace);
                if (isset($keyValue['ИдТипаЦены'])) {
                    $price->type_id = $keyValue['ИдТипаЦены'];
                }
                if (isset($keyValue['Представление'])) {
                    $price->name = $keyValue['Представление'];
                }
                if (isset($keyValue['ЦенаЗаЕдиницу'])) {
                    $price->price = $keyValue['ЦенаЗаЕдиницу'];
                }
                if (isset($keyValue['Валюта'])) {
                    $price->currency = $keyValue['Валюта'];
                }
                if (isset($keyValue['Единица'])) {
                    $price->unit = $keyValue['Единица'];
                }
                if (isset($keyValue['Коэффициент'])) {
                    $price->coefficient = $keyValue['Коэффициент'];
                }
                return $price;
            },
        );

        return $service;
    }


}