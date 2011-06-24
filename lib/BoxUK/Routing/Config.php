<?php

namespace BoxUK\Routing;

/**
 * Manages configuration options for the library.
 * 
 * @copyright Copyright (c) 2010, Box UK
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link http://github.com/boxuk/boxuk-routing
 * @since 1.1
 */
class Config {
    
    /**
     * @var string
     */
    private $routesFile;

    /**
     * @var string
     */
    private $extension;
    
    /**
     * @var string
     */
    private $siteDomain;

    /**
     * @var string
     */
    private $siteWebRoot = '/';
    
    /**
     * @var string
     */
    private $cacheDirectory;
    
    /**
     * Sets the routes file to parse routes from
     * 
     * @param string $routesFile Path to routes file
     * 
     * @throws \InvalidArgumentException
     */
    public function setRoutesFile( $routesFile ) {
        
        if( ! is_string( $routesFile ) ) {
            throw new \InvalidArgumentException('Expected a string');
        }
        
        if( ! is_file( $routesFile ) || ! is_readable( $routesFile ) ) {
            throw new \InvalidArgumentException('Routes file must be a readable file');
        }
        
        $this->routesFile = $routesFile;
        
    }
    
    /**
     * Returns the routes file that has been set, or null if it hasn't
     * 
     * @return string
     */
    public function getRoutesFile() {
        
        return $this->routesFile;
        
    }
    
    /**
     * Set the URL extension to use 
     * 
     * @param string $extension eg. '.html'
     * 
     * @throws \InvalidArgumentException
     */
    public function setExtension( $extension ) {
        
        if( ! is_string( $extension ) ) {
            throw new \InvalidArgumentException('Expected a string');
        }
        
        $this->extension = $extension;

    }

    /**
     * Return the configured URL extension, or null
     * 
     * @return string
     */
    public function getExtension() {
        
        return $this->extension;
        
    }

    /**
     * Sets the site domain to use
     * 
     * @param string $siteDomain eg. mydomain.com
     *
     * @throws \InvalidArgumentException
     */
    public function setSiteDomain( $siteDomain ) {
        
        if( ! is_string( $siteDomain ) ) {
            throw new \InvalidArgumentException('Expected a string');
        }
        
        $this->siteDomain = $siteDomain;

    }

    /**
     * Returns the domain, or null if not set
     * 
     * @return string
     */
    public function getSiteDomain() {
        
        return $this->siteDomain;
        
    }

    /**
     * Set the web root for the application
     * 
     * @param string $siteWebRoot eg. /base/path/
     * 
     * @throws \InvalidArgumentException
     */
    public function setSiteWebRoot( $siteWebRoot ) {
        
        if( ! is_string( $siteWebRoot ) ) {
            throw new \InvalidArgumentException('Expected a string');
        }

        $this->siteWebRoot = $siteWebRoot;
        
    }
    
    /**
     * Return the configured web root, or null
     * 
     * @return string
     */
    public function getSiteWebRoot() {
        
        return $this->siteWebRoot;
        
    }
    
    /**
     * Sets the directory to store cached routes
     * 
     * @param string $cacheDirectory Path to cache directory
     * 
     * @throws \InvalidArgumentException
     */
    public function setCacheDirectory( $cacheDirectory ) {
        
        if( ! is_string( $cacheDirectory ) ) {
            throw new \InvalidArgumentException('Expected a string');
        }
        
        if( ! is_dir( $cacheDirectory ) || ! is_writeable( $cacheDirectory ) ) {
            throw new \InvalidArgumentException('Cache directory must be a writeable directory');
        }
        
        $this->cacheDirectory = $cacheDirectory;
    }
    
    /**
     * Returns the cache directory if set, or null otherwise
     * 
     * @return string
     */
    public function getCacheDirectory() {
        
        return $this->cacheDirectory;
        
    }
    
}
