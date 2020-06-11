<?php
namespace pro_algolia;

class sort
{
    /**
     * Sort array and it's values recursivelly
     *
     * https://stackoverflow.com/a/4501406/6555278
     *
     * @param array $array
     * @return void
     */
    public static function sortRecurvice(&$array)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                static::sortRecurvice($value);
            }
        }
        // sort the values
        array_multisort($array, SORT_ASC, SORT_REGULAR);
        // sort the keys (associative arrays mainly)
        ksort($array, SORT_ASC);
    }
}
