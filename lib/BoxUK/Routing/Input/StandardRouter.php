<?php

namespace BoxUK\Routing\Input;

use BoxUK\Routing\Specification,
    BoxUK\Routing\Config;

/**
 * @ScopeSingleton(implements="BoxUK\Routing\Input\Router")
 * 
 * @copyright Copyright (c) 2010, Box UK
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link http://github.com/boxuk/boxuk-routing
 * @since 1.0
 */
class StandardRouter implements Router {

    /**
     * @var BoxUK\Routing\Config Library config
     */
    private $config;
    
    /**
     * @var array Route specifications
     */
    private $routeSpecs;

    /**
     * @var array Route types
     */
    private $routeTypes;

    /**
     * Creates a new RequestRouter
     *
     */
    public function __construct( Config $config ) {

        $this->config = $config;
        $this->routeSpecs = array();
        $this->routeTypes = array();

    }

    /**
     * Init the router with some routes
     *
     * @param array $routeSpecs
     * @param array $routeTypes
     */
    public function init( array $routeSpecs, array $routeTypes ) {

        $this->routeSpecs = $routeSpecs;
        $this->routeTypes = $routeTypes;

    }

    /**
     * Process route specs, updates the request object and returns if a match was made
     *
     * @param Request $request
     * @param string $url
     *
     * @return Specification
     */
    public function process( Request $request, $url ) {

        if ( $url == '/index.php' ) { $url = '/'; }

        $siteWebRoot = $this->config->getSiteWebRoot();
        $extension = $this->config->getExtension();
        
        if ( $siteWebRoot && strpos($url,$siteWebRoot) === 0 ) {
            $url = substr( $url, strlen($siteWebRoot) );
            if ( substr($url,0,1) != '/' ) {
                $url = "/$url";
            }
        }

        if ( $extension ) {
            $url = preg_replace( "/^(.*)\.{$extension}$/", '$1', $url );
        }

        foreach ( $this->routeSpecs as $specification ) {

            if ( $specification->getMethod() && ($specification->getMethod() != $request->getMethod()) ) {
                continue;
            }

            $route = $specification->getRoute();
            $routeRegExp = $this->buildRegExp( $route );

            if ( preg_match('#^'.$routeRegExp.'(\?|&|$)#',$url,$matches) ) {
                $this->processRouteSpec( $request, $specification, $matches );
                return $specification;
            }

        }

        return false;

    }

    /**
     * Process a single route spec and updates the request object
     *
     * @param Request $request
     * @param Specification $specification
     * @param array $matches
     */
    protected function processRouteSpec( Request $request, Specification $specification, array $matches ) {

        $request->setValue( 'controller', $specification->getController() );
        $request->setValue( 'action', $request->getValue('action',$specification->getAction()) );

        $routeParams = $specification->getParameters();
        $index = 1;

        foreach ( $routeParams as $name => $default ) {

            $value = isset($matches[$index]) && $matches[$index]
                ? urldecode( $matches[$index] )
                : $default;

            $request->setValue( $name, $value );

            $index++;
            
        }

    }

    /**
     * Replaces shortcuts in a route (eg. ':num' and ':word') with their regexp
     *
     * @param string $route
     * @return string
     */
    protected function buildRegExp( $route ) {

        // special regexp characters

        $specialChars = array( '.', '+', '*', '(', ')' );

        foreach ( $specialChars as $special ) {
            $route = str_replace( $special, '\\' . $special, $route );
        }

        // route types

        foreach ( $this->routeTypes as $key => $value ) {
            $route = str_replace( ':' . $key, $value, $route );
        }

        // for legacy route format without a / prefixed
        if ( substr($route,0,1) != '/' ) { $route = '/' . $route; }

        return $route . '/?';

    }

}