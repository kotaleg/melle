<?php
namespace pro_algolia;

class hash
{
    public static function hashItemData($data)
    {
        $json = @json_encode($data);
        return hash('sha256', $json);
    }

    public static function countBytesInItemData($data)
    {
        $json = @json_encode($data);
        return ini_get('mbstring.func_overload') ? mb_strlen($json , '8bit') : strlen($json);
    }
}
