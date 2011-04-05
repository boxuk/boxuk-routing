<?php

namespace BoxUK;

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
     * @var string
     */
    private $routesFile;

    /**
     * @var string
     */
    private $extension;

    /**
     * @var array Cache of routing information
     */
    private $routes;

    /**
     * @var string
     */
    private $siteDomain;

    /**
     * @var string
     */
    private $siteWebRoot = '/';

    /**
     * Sets the path to the routes file to use
     *
     * @param string $routesFile
     */
    public function setRoutesFile( $routesFile ) {
        
        $this->routesFile = $routesFile;

    }

    /**
     * Sets the URL extension to use
     *
     * @param string $extension
     */
    public function setExtension( $extension ) {
        
        $this->extension = $extension;

    }

    /**
     * Sets the site domain to use
     *
     * @param string $siteDomain
     */
    public function setSiteDomain( $siteDomain ) {
        
        $this->siteDomain = $siteDomain;

    }

    /**
     * Sets the site web root to use
     *
     * @param string $siteWebRoot
     */
    public function setSiteWebRoot( $siteWebRoot ) {

        $this->siteWebRoot = $siteWebRoot;
        
    }

    /**
     * Creates and returns a routing object
     * 
     * @return \BoxUK\Routing\Input\StandardRouter
     */
    public function getRouter() {
        
        list( $routeSpecs, $routeTypes ) = $this->getRoutes();

        $router = new \BoxUK\Routing\Input\StandardRouter();
        $router->setExtension( $this->extension );
        $router->setSiteWebRoot( $this->siteWebRoot );
        $router->init( $routeSpecs, $routeTypes );

        return $router;

    }

    /**
     * Creates and returns a filter object
     * 
     * @return \BoxUK\Routing\Output\StandardFilter
     */
    public function getFilter() {

        return new \BoxUK\Routing\Output\StandardFilter(
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

        $rewriter = new \BoxUK\Routing\StandardRewriter();
        $rewriter->setExtension( $this->extension );
        $rewriter->init( $routeSpecs, $routeTypes, $this->siteDomain, $this->siteWebRoot );

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

            if ( $this->routesFile ) {
                $parser = new \BoxUK\Routing\Specification\CachingParser();
                $this->routes = $parser->parseFile( $this->routesFile );
            }

            else {
                $this->routes = array( array(), array() );
            }

        }

        return $this->routes;

    }

}
