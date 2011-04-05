<?php

namespace BoxUK\Routing\Specification;

/**
 * Handles caching of parsed route files
 * 
 * @copyright Copyright (c) 2010, Box UK
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link http://github.com/boxuk/boxuk-routing
 * @since 0.1.7
 */
class CachingParser extends StandardParser {

    /**
     * Caches the parsed spec results and invalidates the cache if the source
     * file is updated.
     *
     * @param string $path
     *
     * @return array
     */
    public function parseFile( $path ) {
        
        $cacheFile = crc32( $path );
        $cacheDir = sys_get_temp_dir();
        $cachePath = sprintf( '%s/%s.cache', $cacheDir, $cacheFile );

        if ( file_exists($cachePath) && (filemtime($path) <= filemtime($cachePath)) ) {
            return unserialize( file_get_contents($cachePath) );
        }

        else{
            $results = parent::parseFile( $path );
            file_put_contents( $cachePath, serialize($results) );
            return $results;
        }
        
    }

}
