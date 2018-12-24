<?php
class ModelToolHelp extends Model
{
    /**
     * Recursively empty and delete a directory
     *
     * @param string $path
     * @ref https://gist.github.com/jmwebservices/986d9b975eb4deafcb5e2415665f8877
     */
    public static function rrmdir( string $path ) : void
    {
        if( trim( pathinfo( $path, PATHINFO_BASENAME ), '.' ) === '' )
            return;
        if( is_dir( $path ) )
        {
            array_map( 'ModelToolHelp::rrmdir', glob( $path . DIRECTORY_SEPARATOR . '{,.}*', GLOB_BRACE | GLOB_NOSORT ) );
            @rmdir( $path );
        }
        else
            @unlink( $path );
    }
}

