<?php

namespace BoxUK;

use \BoxUK\Routing\Config,
    \BoxUK\Routing\Output\StandardFilter,
    \BoxUK\Routing\StandardRewriter,
    \BoxUK\Routing\Specification\CachingParser;

/**
 * Helper class for creating the main objects like the router, filter, etc...
 *
 * @copyright Copyright (c) 2010, Box UK
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link http://github.com/boxuk/boxuk-routing
 * @since 1.0
 */
class Routing {

    /**
     * @var array Cache of routing information
     */
    private $routes;
    
    /**
     * @var \BoxUK\Routing\Config
     */
    private $config;
    
    /**
     * Creates a new instance
     * 
     * @param \BoxUK\Routing\Config $config
     */
    public function __construct(Config $config) {
        $this->config = $config;
    }
    
    /**
     * Returns the configuration object used by the library
     * 
     * @return \BoxUK\Routing\Config
     */
    public function getConfiguration() {
        return $this->config;
    }

    /**
     * Sets the configuration object used by the library
     *
     * @param \BoxUK\Routing\Config $config 
     */
    public function setConfiguration(Config $config) {
        $this->config = $config;
    }
    
    /**
     * Creates and returns a routing object
     * 
     * @return \BoxUK\Routing\Input\StandardRouter
     */
    public function getRouter() {
        
        list( $routeSpecs, $routeTypes ) = $this->getRoutes();

        $router = new \BoxUK\Routing\Input\StandardRouter();
        $router->setExtension( $this->config->getExtension() );
        $router->setSiteWebRoot( $this->config->getSiteWebRoot() );
        $router->init( $routeSpecs, $routeTypes );

        return $router;

    }

    /**
     * Creates and returns a filter object
     * 
     * @return \BoxUK\Routing\Output\StandardFilter
     */
    public function getFilter() {

        return new StandardFilter(
            $this->getRewriter()
        );

    }

    /**
     * Creates and returns a rewriter object
     *
     * @return \BoxUK\Routing\StandardRewriter
     */
    public function getRewriter() {
    
        list( $routeSpecs, $routeTypes ) = $this->getRoutes();

        $rewriter = new StandardRewriter();
        $rewriter->setExtension( $this->config->getExtension() );
        $rewriter->init( 
            $routeSpecs, 
            $routeTypes, 
            $this->config->getSiteDomain(), 
            $this->config->getSiteWebRoot() 
        );

        return $rewriter;

    }

    /**
     * Returns route specs and types to use to configure objects, this will
     * use routes from the routes file if it has been specified.
     *
     * @return array
     */
    protected function getRoutes() {

        if ( !$this->routes ) {
            
            $routesFile = $this->config->getRoutesFile();

            if ( $routesFile ) {
                $parser = new CachingParser();
                
                $cacheDir = $this->config->getCacheDirectory();
                if($cacheDir) {
                    $parser->setCacheDirectory($cacheDir);
                }
                
                $this->routes = $parser->parseFile( $routesFile );
            }

            else {
                $this->routes = array( array(), array() );
            }

        }

        return $this->routes;

    }

}