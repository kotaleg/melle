<?php
// Version
define('VERSION', '3.0.2.0');

// Configuration
$config_file = 'config.php';
if (isset($_SERVER['SERVER_ADDR'])) {
    if (isset($_SERVER['SERVER_NAME'])
    && strpos($_SERVER['SERVER_NAME'], '.test')) {
        $config_file = 'config.local.php';
    } elseif (in_array($_SERVER['SERVER_ADDR'], array('::1'))) {
        $config_file = 'config.local.php';
    }
}

if (is_file($config_file)) {
    require_once($config_file);
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

start('admin');
