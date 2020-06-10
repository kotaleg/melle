<?php
namespace pro_algolia;

class hash
{
    public static function hashItemData($data)
    {
        $json = @json_encode($data);
        return hash('sha256', $json);
    }
}
