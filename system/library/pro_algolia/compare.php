<?php
namespace pro_algolia;

class compare
{
    /**
     * Returns an array with the differences between $array1 and $array2
     *
     * @param array $aArray1
     * @param array $aArray2
     * @return array
     *
     * @copyright Roger Vilà (https://github.com/rogervila)
     */
    public static function compareArrays($array1, $array2)
    {
        $result = array();

        foreach ($array1 as $key => $value) {
            if (!is_array($array2) || !array_key_exists($key, $array2)) {
                $result[$key] = $value;
                continue;
            }

            if (is_array($value)) {
                $recursiveArrayDiff = static::compareArrays($value, $array2[$key]);

                if (count($recursiveArrayDiff)) {
                    $result[$key] = $recursiveArrayDiff;
                }

                continue;
            }

            if ($value != $array2[$key]) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
