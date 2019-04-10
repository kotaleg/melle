<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* IMPORT HACK START */
if (isset($_REQUEST['route'])
&& substr($_REQUEST['route'], 0, 4) == 'api/') {
    ini_set('max_execution_time', 600);
}
/* IMPORT HACK END */

// Version
define('VERSION', '3.0.2.0');

// Configuration
$config_file = 'config.php';
if (isset($_SERVER['SERVER_ADDR'])
&& in_array($_SERVER['SERVER_ADDR'], array('127.0.0.1', '::1'))) {
    $config_file = 'config.local.php';
}

if (is_file($config_file)) {
    require_once($config_file);
}

// Install
// if (!defined('DIR_APPLICATION')) {
//     header('Location: install/index.php');
//     exit;
// }

// Startup
require_once(DIR_SYSTEM . 'startup.php');

start('catalog');