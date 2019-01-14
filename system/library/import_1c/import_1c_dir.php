<?php
namespace import_1c;
require_once __DIR__ . '/vendor/autoload.php';

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
}

