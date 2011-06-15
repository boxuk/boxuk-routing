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
class Configuration extends \PHPUnit_Framework_TestCase {
    
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
     * @param string $routesFile
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
     * @return string
     */
    public function getRoutesFile() {
        return $this->routesFile;
    }
    
    /**
     * @param string $extension
     * 
     * @throws \InvalidArgumentException
     */
    public function setExtension( $extension ) {
        if( ! is_string( $extension ) ) {
            throw new \InvalidArgumentException('Expected a string');
        }
        
        $this->extension = $extension;

    }

    public function getExtension() {
        return $this->extension;
    }

    /**
     * Sets the site domain to use
     *
     * @param string $siteDomain
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
     * @return string
     */
    public function getSiteDomain() {
        return $this->siteDomain;
    }

    /**
     * @param string $siteWebRoot
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
     * @return string
     */
    public function getSiteWebRoot() {
        return $this->siteWebRoot;
    }
    
    /**
     * @param string $cacheDirectory
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
     * @return string
     */
    public function getCacheDirectory() {
        return $this->cacheDirectory;
    }
    
}