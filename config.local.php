<?php
// HTTP
define('HTTP_SERVER', 'http://melle.test/');

// HTTPS
define('HTTPS_SERVER', 'http://melle.test/');

// HTTP OPT
define('HTTP_OPT', HTTPS_SERVER.'opt/');

// DIR
define('DIR_OPT', '/Users/ivan/repos/melle/opt/');
define('DIR_APPLICATION', '/Users/ivan/repos/melle/catalog/');
define('DIR_SYSTEM', '/Users/ivan/repos/melle/system/');
define('DIR_DATABASE', '/Users/ivan/repos/melle/system/database/');
define('DIR_LANGUAGE', '/Users/ivan/repos/melle/catalog/language/');
define('DIR_TEMPLATE', '/Users/ivan/repos/melle/catalog/view/theme/');
define('DIR_CONFIG', '/Users/ivan/repos/melle/system/config/');
define('DIR_IMAGE', '/Users/ivan/repos/melle/image/');
define('DIR_DOWNLOAD', '/Users/ivan/repos/melle/system/storage/download/');
define('DIR_CACHE', '/Users/ivan/repos/melle/system/storage/cache/');
define('DIR_LOGS', '/Users/ivan/repos/melle/system/storage/logs/');

define('DIR_MODIFICATION', '/Users/ivan/repos/melle/system/storage/modification/');
define('DIR_UPLOAD', '/Users/ivan/repos/melle/system/storage/upload/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_SESSION', '/Users/ivan/repos/melle/system/storage/session/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');
define('DB_DATABASE', 'melle-work');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');
?>
