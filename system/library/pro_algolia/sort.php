<?php
namespace pro_algolia;

class sort
{
    public static function sortRecurvice(&$array)
    {
        foreach ($array as &$value) {
           if (is_array($value)) {
                static::sortRecurvice($value);
           }
        }
        return array_multisort($array, SORT_ASC, SORT_REGULAR);
    }
}

