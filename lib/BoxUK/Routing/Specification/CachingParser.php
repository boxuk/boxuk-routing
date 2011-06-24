<?php

namespace BoxUK\Routing\Specification;

use BoxUK\Routing\Config;

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
     * @var BoxUK\Routing\Config
     */
    private $config;
    
    /**
     * Create a new caching parser object
     * 
     */
    public function __construct( Config $config ) {
        
        $this->config = $config;
        
    }

    /**
     * Returns the configured cache directory if set, or the system default
     * 
     * @throws InvalidArgumentException
     * 
     * @return string
     */
    protected function getCacheDirectory() {
        
        $cacheDir = $this->config->getCacheDirectory();
        
        if ( !$cacheDir ) {
            $cacheDir = sys_get_temp_dir();
        }
        
        if ( !is_dir($cacheDir) || !is_writeable($cacheDir) ) {
            throw new \InvalidArgumentException( 'Cache directory must be a writeable directory' );
        }

        return $cacheDir;
        
    }

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
        $cacheDir = $this->getCacheDirectory();
        $cachePath = sprintf( '%s/%s.cache', $cacheDir, $cacheFile );

        if ( file_exists($cachePath) && (filemtime($path) <= filemtime($cachePath)) ) {
            return unserialize( file_get_contents($cachePath) );
        }

        else {
            $results = parent::parseFile( $path );
            file_put_contents( $cachePath, serialize($results) );
            return $results;
        }
        
    }

}
