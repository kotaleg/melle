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
if (is_file('config.php')) {
    require_once('config.php');
}

// Install
if (!defined('DIR_APPLICATION')) {
    header('Location: install/index.php');
    exit;
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

start('catalog');