<?php
// HTTP
define('HTTP_SERVER', 'http://test.melle.online/');

// HTTPS
define('HTTPS_SERVER', 'http://test.melle.online/');

// HTTP OPT
define('HTTP_OPT', HTTPS_SERVER.'opt/');

// DIR
define('DIR_OPT', '/Users/ivan/repos/melle/opt/');
define('DIR_APPLICATION', '/home/web/test.melle.online/www/catalog/');
define('DIR_SYSTEM', '/home/web/test.melle.online/www/system/');
define('DIR_DATABASE', '/home/web/test.melle.online/www/system/database/');
define('DIR_LANGUAGE', '/home/web/test.melle.online/www/catalog/language/');
define('DIR_TEMPLATE', '/home/web/test.melle.online/www/catalog/view/theme/');
define('DIR_CONFIG', '/home/web/test.melle.online/www/system/config/');
define('DIR_IMAGE', '/home/web/test.melle.online/www/image/');
define('DIR_DOWNLOAD', '/home/web/test.melle.online/www/system/storage/download/');
define('DIR_CACHE', '/home/web/test.melle.online/www/system/storage/cache/');
define('DIR_LOGS', '/home/web/test.melle.online/www/system/storage/logs/');

define('DIR_MODIFICATION', '/home/web/test.melle.online/www/system/storage/modification/');
define('DIR_UPLOAD', '/home/web/test.melle.online/www/system/storage/upload/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_SESSION', '/home/web/test.melle.online/www/system/storage/session/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'melle');
define('DB_PASSWORD', 'DA3NFzABRoFnnzjt');
define('DB_DATABASE', 'melle-test');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');
?>