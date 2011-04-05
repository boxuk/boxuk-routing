<?php

if ( !function_exists('boxuk_autoload') ) {
    function boxuk_autoload( $rootDir ) {
        spl_autoload_register(function( $className ) use ( $rootDir ) {
            $file = sprintf(
                '%s/%s.class.php',
                $rootDir,
                str_replace( '\\', '/', $className )
            );
            if ( file_exists($file) ) {
                require $file;
            }
        });
    }
}

boxuk_autoload( __DIR__ );
