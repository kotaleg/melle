<?php
// HTTP
define('HTTP_SERVER', 'https://oc.test/302/melle-store/admin/');
define('HTTP_CATALOG', 'https://oc.test/302/melle-store/');

// HTTPS
define('HTTPS_SERVER', 'https://oc.test/302/melle-store/admin/');
define('HTTPS_CATALOG', 'https://oc.test/302/melle-store/');

// HTTP OPT
define('HTTP_OPT', HTTPS_CATALOG.'opt/');
define('HTTP_OPT_ADMIN', HTTPS_CATALOG.'opt/admin/');

// DIR
define('DIR_OPT', '/Users/ivan/Documents/repos/oc/302/melle-store/opt/');
define('DIR_APPLICATION', '/Users/ivan/Documents/repos/oc/302/melle-store/admin/');
define('DIR_SYSTEM', '/Users/ivan/Documents/repos/oc/302/melle-store/system/');
define('DIR_DATABASE', '/Users/ivan/Documents/repos/oc/302/melle-store/system/database/');
define('DIR_LANGUAGE', '/Users/ivan/Documents/repos/oc/302/melle-store/admin/language/');
define('DIR_TEMPLATE', '/Users/ivan/Documents/repos/oc/302/melle-store/admin/view/template/');
define('DIR_CONFIG', '/Users/ivan/Documents/repos/oc/302/melle-store/system/config/');
define('DIR_IMAGE', '/Users/ivan/Documents/repos/oc/302/melle-store/image/');
define('DIR_DOWNLOAD', '/Users/ivan/Documents/repos/oc/302/melle-store/system/storage/download/');
define('DIR_CACHE', '/Users/ivan/Documents/repos/oc/302/melle-store/system/storage/cache/');
define('DIR_LOGS', '/Users/ivan/Documents/repos/oc/302/melle-store/system/storage/logs/');

define('DIR_MODIFICATION', '/Users/ivan/Documents/repos/oc/302/melle-store/system/storage/modification/');
define('DIR_UPLOAD', '/Users/ivan/Documents/repos/oc/302/melle-store/system/storage/upload/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_SESSION', '/Users/ivan/Documents/repos/oc/302/melle-store/system/storage/session/');

define('DIR_CATALOG', '/Users/ivan/Documents/repos/oc/302/melle-store/catalog/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', '127.0.0.1');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');
define('DB_DATABASE', 'melle-store');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');
// OpenCart API
define('OPENCART_SERVER', 'https://www.opencart.com/');
?>
