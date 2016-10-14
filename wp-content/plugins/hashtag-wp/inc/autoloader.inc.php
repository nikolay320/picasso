<?php

set_include_path( implode( PATH_SEPARATOR, array(get_include_path(), realpath( plugin_dir_path( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR ) ) );

if( ! function_exists( 'glcdesign_autoloader' ) )
{

    function glcdesign_autoloader( $class_name )
    {

        if ( false !== strpos( $class_name, 'Glcdesign' ) )
        {
            $class_file = str_replace( '\\', DIRECTORY_SEPARATOR, $class_name ) . '.php';
            require_once $class_file;
        }

    }

    //registering the autoloader
    spl_autoload_register( 'glcdesign_autoloader' );

}