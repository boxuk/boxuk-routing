<?php

namespace BoxUK\Routing;

/**
 * This class uses route specifications to try and rewrite urls for them
 *
 * @copyright Copyright (c) 2010, Box UK
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link http://github.com/boxuk/boxuk-routing
 * @since 1.0
 */
class StandardRewriter implements Rewriter {

    /**
     * @var BoxUK\Routing\Config Library config
     */
    private $config;
    
    /**
     * @var array Route specifications
     */
    private $routeSpecs;

    /**
     * @var array Route data types
     */
    private $routeTypes;

    /**
     * Creates a new object for rewriting url based on routes
     *
     */
    public function __construct( Config $config ) {

        $this->config = $config;
        $this->routeSpecs = array();
        $this->routeTypes = array();

    }

    /**
     * Init with some routes and types
     * 
     * @param array $routeSpecs
     * @param array $routeTypes
     * @param string $siteDomain
     * @param string $siteWebRoot
     */
    public function init( array $routeSpecs, array $routeTypes ) {

        $this->routeSpecs = $routeSpecs;
        $this->routeTypes = $routeTypes;
        

    }

    /**
     * Tries to rewrite a url using the routes specs we have.  if no routes
     * are matched the the original url is returned.
     *
     * @param string $url
     * @param bool $includeDomain (false)
     *
     * @return string
     */
    public function rewrite( $url, $includeDomain = false ) {

        if ( !$this->domainMismatch($url) ) {

            $rewriteInfo = $this->getRewriteInfo( $url );

            if ( $rewriteInfo != null ) {

                list( $domain, $webRoot, $url, $queryString ) = $rewriteInfo;

                return sprintf(
                    '%s%s%s%s',
                    $includeDomain ? $domain : '',
                    $webRoot,
                    $url,
                    $queryString
                );

            }

        }
        
        return $url;

    }

    /**
     * If a domain has been set, this checks it doesn't conflict with any that
     * has been specified in the URL.
     *
     * @param string $url
     *
     * @return bool
     */
    protected function domainMismatch( $url ) {

        $siteDomain = $this->config->getSiteDomain();
        
        if ( $siteDomain && substr($url,0,4) == 'http' ) {
            return strpos( $url, '://' . $siteDomain ) === false;
        }

        return false;

    }

    /**
     * Tries to rewrite a URL, and if it succeeds returns an array of the form...
     *
     * array(
     *     'domain.com',            // domain
     *     '/site/web/root/',       // web root
     *     'user/login',            // url part
     *     '?foo=bar#fragment',     // query string
     *     $oSpec                   // route spec matched
     * )
     *
     * If it doesn't work, returns null.
     *
     * @param string $url
     *
     * @return array
     */
    public function getRewriteInfo( $url ) {

        foreach ( $this->routeSpecs as $specification ) {

            $regexp = '/[\?&]controller=' . $specification->getController() . '(&|#|$)/i';

            if ( preg_match($regexp,$url) ) {

                $matchedParams = $this->getMatchedParams( $specification, $url );

                if ( $matchedParams !== null ) {
                    return array(
                        'http://' . $this->config->getSiteDomain(),
                        $this->config->getSiteWebRoot(),
                        $this->getUrl( $specification, $matchedParams ),
                        $this->getQueryString( $specification, $url, $matchedParams ),
                        $specification
                    );
                }

            }

        }

        return null;
        
    }

    /**
     * Creates the root url to rewrite to (excludes query string where other
     * are are added).  This should also NOT include a slash prefix as this
     * will be handled by the web root before it.
     *
     * @param Specification $oSpec
     * @param array $matchedParams
     *
     * @return string
     */
    protected function getUrl( $oSpec, $matchedParams ) {

        $extension = $this->config->getExtension();
        $route = $oSpec->getRoute();
        $index = 0;

        $params = array_keys( $oSpec->getParameters() );

        while ( preg_match('/(:\w+)/',$route,$matches) ) {
            list( $IGNORE, $param ) = $matches;
            $pos = strpos( $route, $param );
            $route = substr( $route, 0, $pos )
                     . $matchedParams[ $params[$index++] ] .
                     substr( $route, $pos + strlen($param) );
        }

        if ( substr($route,0,1) == '/' ) {
            $route = substr( $route, 1 );
        }
        
        if ( $extension ) {
            $route = sprintf( '%s.%s', $route, $extension );
        }

        return $route;

    }

    /**
     * Checks the parameters specified by the route to see if they match the
     * url being processed.  returns null if the matching failed, and an array
     * of matched parameters (possibly empty).
     *
     * @param Specification $specification
     * @param string $url
     *
     * @return array
     */
    protected function getMatchedParams( $specification, $url ) {

        $matchedParams = array();
        $params = $specification->getParameters();
        $paramsToMatch = $this->getParamsToMatch( $specification );

        foreach ( $paramsToMatch as $param => $type ) {

            $regexp = '/[\?&;]' . $param . '=([a-z0-9%,-.+]+)/i';

            if ( preg_match($regexp,$url,$matches) ) {

                $value = $matches[ 1 ];
                $default = !empty( $params[$param] )
                    ? $params[ $param ]
                    : '';

                if ( $this->paramMatches($type,$value,$default) ) {
                    $matchedParams[ $param ] = $value;
                }

                else { return null; }

            }

            else { return null; }

        }

        // if an action has been specified in the route AND the params
        // then they need to match for the route to be valid.
        if ( isset($matchedParams['action']) && $specification->getAction() != 'index'
                 && $matchedParams['action'] != $specification->getAction() ) {
            return null;
        }

        return $matchedParams;

    }

    /**
     * Checks a value matches a type (number or word), and that it matches the
     * default if one was specified.  the type can possibly be blank where it
     * is assumed to match.
     *
     * @param string $type
     * @param mixed $value
     *
     * @return boolean
     */
    protected function paramMatches( $type, $value, $default ) {

        if ( !empty($default) && $default != $value ) {
            return false;
        }

        return isset( $this->routeTypes[$type] )
            ? preg_match( '/^' . $this->routeTypes[$type] . '$/', $value )
            : !empty( $default );

    }

    /**
     * Returns the parameters to match for a route as name => type pairs.
     *
     * @param Specification $specification
     *
     * @return array
     */
    protected function getParamsToMatch( $specification ) {

        $params = $specification->getParameters();
        $paramsToMatch = array(); // name => value
        $matchNames = array_keys( $params );

        preg_match_all( '/:(\w+)/', $specification->getRoute(), $matchTypes );

        for ( $i=0; $i<count($matchNames); $i++ ) {
            $paramsToMatch[ $matchNames[$i] ] = isset( $matchTypes[ 1 ][ $i ] )
                ? $matchTypes[ 1 ][ $i ]
                : '';
        }

        if ( $specification->getAction() != 'index' ) {
            $paramsToMatch[ 'action' ] = 'word';
        }

        return $paramsToMatch;

    }

    /**
     * Creates the query string to be added to the new url.  this should not
     * include any of the arguments _specified or implied_ by the new url.
     * This also preserves any fragment defined.
     *
     * @param Specification $specification
     * @param string $url
     * @param array $matchedParams
     *
     * @return string
     */
    protected function getQueryString( $specification, $url, $matchedParams ) {

        $newQueryString = '';

        list( $root, $queryString ) = explode( '?', $url, 2 );

        if ( $queryString ) {

            @list( $queryString, $fragment ) = explode( '#', $queryString );

            $queryString = html_entity_decode( $queryString );
            $ignoreParams = array_keys( $matchedParams );
            $ignoreParams[] = 'controller'; // always implied in route

            // add params to new query string

            $newQueryString = '';
            $pairs = explode( '&', $queryString );

            foreach ( $pairs as $pair ) {
                if ( strstr($pair,'=') !== false ) {

                    list( $name, $value ) = explode( '=', $pair );

                    if ( !in_array($name,$ignoreParams) && !empty($value) ) {

                        // ignore the action parameter if it's implied in the route
                        if ( $name == 'action' && $value == $specification->getAction() ) { continue; }

                        $newQueryString .= $newQueryString ? '&' : '';
                        $newQueryString .= sprintf( '%s=%s', $name, $value );

                    }

                }
            }

            if ( $newQueryString ) {
                $newQueryString = '?' . $newQueryString;
            }

            if ( $fragment ) {
                $newQueryString .= '#' . $fragment;
            }

        }

        return $newQueryString;

    }

}
