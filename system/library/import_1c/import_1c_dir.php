<?php
namespace import_1c;
require_once __DIR__ . '/vendor/autoload.php';

use Lead\Dir\Dir;

class import_1c_dir
{
    public static function createDir($path)
    {
        return Dir::make($path,
            array(
                'mode'      => 0755,
                'recursive' => true,
            )
        );
    }

    public static function clearDir($path, $exclude = array())
    {
        return Dir::remove($path,
            array(
                'followSymlinks' => false,
                'recursive'      => true,
                'exclude'        => $exclude,
            )
        );
    }

    public static function scanDir($path, $include = '*')
    {
        return Dir::scan($path,
            array(
                'followSymlinks' => false,
                'recursive'      => true,
                'skipDots'       => true,
                'include'        => $include,
            )
        );
    }
}

