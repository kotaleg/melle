<?php
// HTTP
define('HTTP_SERVER', 'http://oc.test/302/melle-store/');

// HTTPS
define('HTTPS_SERVER', 'http://oc.test/302/melle-store/');

// HTTP OPT
// define('HTTP_OPT', HTTPS_SERVER.'opt/');

// DIR
// define('DIR_OPT', '/Users/ivan/Documents/repo/oc/302/melle-opt/');
define('DIR_APPLICATION', '/Users/ivan/Documents/repo/oc/302/melle-store/catalog/');
define('DIR_SYSTEM', '/Users/ivan/Documents/repo/oc/302/melle-store/system/');
define('DIR_DATABASE', '/Users/ivan/Documents/repo/oc/302/melle-store/system/database/');
define('DIR_LANGUAGE', '/Users/ivan/Documents/repo/oc/302/melle-store/catalog/language/');
define('DIR_TEMPLATE', '/Users/ivan/Documents/repo/oc/302/melle-store/catalog/view/theme/');
define('DIR_CONFIG', '/Users/ivan/Documents/repo/oc/302/melle-store/system/config/');
define('DIR_IMAGE', '/Users/ivan/Documents/repo/oc/302/melle-store/image/');
define('DIR_DOWNLOAD', '/Users/ivan/Documents/repo/oc/302/melle-store/system/storage/download/');
define('DIR_CACHE', '/Users/ivan/Documents/repo/oc/302/melle-store/system/storage/cache/');
define('DIR_LOGS', '/Users/ivan/Documents/repo/oc/302/melle-store/system/storage/logs/');

define('DIR_MODIFICATION', '/Users/ivan/Documents/repo/oc/302/melle-store/system/storage/modification/');
define('DIR_UPLOAD', '/Users/ivan/Documents/repo/oc/302/melle-store/system/storage/upload/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_SESSION', '/Users/ivan/Documents/repo/oc/302/melle-store/system/storage/session/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');
define('DB_DATABASE', 'melle-store');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');
?>
