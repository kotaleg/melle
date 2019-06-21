<?php

// Configuration
$config_file = 'config.php';
if (isset($_SERVER['SERVER_ADDR'])) {
    if (isset($_SERVER['SERVER_NAME'])
    && strpos($_SERVER['SERVER_NAME'], '.test')) {
        $config_file = 'config.local.php';
        define('MELLELOCAL', true);
    } elseif (in_array($_SERVER['SERVER_ADDR'], array('::1'))) {
        $config_file = 'config.local.php';
        define('MELLELOCAL', true);
    }
}

if (is_file($config_file)) {
    require_once($config_file);
}

require_once(DIR_SYSTEM . 'helper/utf8.php');
require_once(DIR_SYSTEM . 'engine/registry.php');
require_once(DIR_SYSTEM . 'library/request.php');
require_once(DIR_SYSTEM . 'library/image.php');

$registry = new registry();
$request = new request();
$resize = new MelleResize();


$json = array();

if (isset($request->get['file'])
&& isset($request->get['w'])
&& isset($request->get['h'])) {
    $file = (string)$request->get['file'];
    $width = (int)$request->get['w'];
    $height = (int)$request->get['h'];
    $custom = (bool) (isset($request->get['c'])) ? $request->get['c'] : false;

    $r = $resize->resizeImage($file, $width, $height, $custom);

    if ($r['resized']) {
        $size = getimagesize($r['resized']);

        if ($size)
        {
            if (in_array($size[2], array(
                IMAGETYPE_PNG,
                IMAGETYPE_JPEG,
                IMAGETYPE_GIF,
                IMAGETYPE_JPEG2000,
                IMAGETYPE_WEBP,
            ))) {
                $fp = fopen($r['resized'], 'rb');
                header('Cache-Control: max-age=2592000');
                header('Content-Type: '.$size['mime']);
                header('X-Dev: ivan-st');
                fpassthru($fp);
                exit;
            }
        }
    }
}

$json['error'] = 'Sorry bro, not allowed';
header('Content-Type: application/json');
echo json_encode($json);
exit;


class MelleResize
{
    public function resizeImage($filename, $width, $height, $custom = false)
    {
        $result = array(
            'type' => false,
            'resized' => false,
        );

        if (!is_file(DIR_IMAGE . $filename) || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $filename)), 0, strlen(DIR_IMAGE)) != str_replace('\\', '/', DIR_IMAGE)) {
            return $result;
        }

        $customPrefix = ($custom) ? '-custom' : '';
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $image_old = $filename;
        $image_new = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.'))
            . '-' . (int)$width . 'x' . (int)$height . $customPrefix . '.' . $extension;

        if (!is_file(DIR_IMAGE . $image_new) || (filemtime(DIR_IMAGE . $image_old) > filemtime(DIR_IMAGE . $image_new))) {
            list($width_orig, $height_orig, $image_type) = getimagesize(DIR_IMAGE . $image_old);

            if (!in_array($image_type, array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF))) {
                $result['resized'] = DIR_IMAGE . $image_old;
                return $result;
            }

            $path = '';

            $directories = explode('/', dirname($image_new));

            foreach ($directories as $directory) {
                $path = $path . '/' . $directory;

                if (!is_dir(DIR_IMAGE . $path)) {
                    @mkdir(DIR_IMAGE . $path, 0777);
                }
            }

            if ($width_orig != $width || $height_orig != $height) {
                $image = new Image(DIR_IMAGE . $image_old);

                if ($custom) {
                    $image->customResize($width, $height);
                } else {
                    $image->resize($width, $height);
                }

                $image->save(DIR_IMAGE . $image_new);
            } else {
                copy(DIR_IMAGE . $image_old, DIR_IMAGE . $image_new);
            }
        }

        $result['resized'] = DIR_IMAGE . $image_new;
        return $result;
    }
}

