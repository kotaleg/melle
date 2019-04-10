<?php
// Version
define('VERSION', '3.0.2.0');

// Configuration
$config_file = 'config.php';
if (isset($_SERVER['SERVER_ADDR'])
&& in_array($_SERVER['SERVER_ADDR'], array('::1'))) {
    $config_file = 'config.local.php';
}

if (is_file($config_file)) {
    require_once($config_file);
}

// Install
// if (!defined('DIR_APPLICATION')) {
//     header('Location: ../install/index.php');
//     exit;
// }

// Startup
require_once(DIR_SYSTEM . 'startup.php');

start('admin');