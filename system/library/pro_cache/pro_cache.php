<?php

namespace pro_cache;
require_once __DIR__ . '/vendor/autoload.php';


class Pro_Cache extends \Stash\Pool
{
    function __construct($cache_path = Null)
    {
        $this->driver = new \Stash\Driver\FileSystem(array(
            'path' => $this->validateCachePath($cache_path)
        ));

        parent::__construct($this->driver);
    }

    private function validateCachePath($cache_path)
    {
        if (!$cache_path) {
            if (defined('DIR_CACHE')) {
                $cache_path = DIR_CACHE;
            } else {
                $cache_path = 'php://temp';
            }
        }

        return $cache_path;
    }
}