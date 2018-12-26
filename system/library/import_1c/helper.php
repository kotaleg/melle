<?php
namespace import_1c;

class helper
{
    public static function parseBool($value)
    {
        return (strcmp(strtolower($value), 'true') === 0) ? true : false;
    }
}