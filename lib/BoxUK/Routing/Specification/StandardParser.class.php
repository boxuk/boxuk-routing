<?php

namespace BoxUK\Routing\Specification;

use BoxUK\Routing\Specification;

/**
 * Class to handle parsing route specifications into RouteSpecification objects
 *
 * Routes are defined in the following format...
 *
 * METHOD URL = CONTROLLER:ACTION( PARAMS, ... )
 *
 * You can use the keys :num (for a number) and :word for a word in the description.
 *
 * Examples:
 *
 * / = category()
 * /message/:num = usermessage:show( id )
 * /groups/:num/discussions/:num = group:show( id:default, discussionId )
 *
 * @copyright Copyright (c) 2010, Box UK
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link http://github.com/boxuk/boxuk-routing
 * @since 1.0
 */
class StandardParser implements Parser {

    /**
     * Parses a file and returns any route specifications found
     * 
     * @param string $path
     *
     * @return array ( routes, types )
     */
    public function parseFile( $path ) {
        
        $routeSpecs = array();
        $lines = file( $path );
        $routeTypes = Specification::$types;
        $controller = null;
        $baseurl = '';
        
        foreach( $lines as $line ) {

            $line = trim( $line );
            $firstChar = substr( $line, 0, 1 );

            switch ( $firstChar ) {

                // comment
                case '#':
                    break;

                // type definition
                case ':':
                    list( $name, $regexp ) = $this->parseType( $line );
                    if ( $name && $regexp ) {
                        $routeTypes[ $name ] = $regexp;
                    }
                    break;

                // start/end of controller block
                case '[':
                    if ( preg_match('/\[(\w+|\*)(:.+)?\]/',$line,$matches) ) {
                        $value = $matches[ 1 ];
                        if ( $value == '*' ) {
                            $controller = null;
                            $baseurl = null;
                        }
                        else {
                            $controller = $value;
                            if ( isset($matches[2]) ) {
                                $baseurl = substr( $matches[2], 1 );
                            }
                        }
                    }
                    break;

                // try and parse spec line
                default:
                    $specification = $controller
                        ? $this->parseControllerSpec( $controller, $line, $baseurl )
                        : $this->parseSpec( $line );
                    if ( $specification != null ) {
                        $routeSpecs[] = $specification;
                    }

            }
            
        }
        
        return array( $routeSpecs, $routeTypes );
        
    }

    /**
     * Parse a route spec type and return the name and regexp
     *
     * @param string $routeType
     *
     * @return array
     */
    protected function parseType( $routeType ) {

        @list( $name, $regexp ) = explode( ' = ', $routeType );

        return array( substr($name,1), $regexp );

    }

    /**
     * Tries to parse a route specification into an object, return null if its
     * not a valid route spec.
     * 
     * @param string $specText
     *
     * @return Specification
     */
    public function parseSpec( $specText ) {

        @list( $route, $spec ) = explode( ' = ', $specText );

        if ( preg_match('/^(\w+):?(\w*)\((.*)\)$/',$spec,$matches) ) {

            list( $IGNORE, $controller, $action, $params ) = $matches;

            $method = Specification::DEFAULT_METHOD;
            $keyedParams = array();
            $params = empty( $params )
                ? array()
                : array_map( 'trim', explode( ',', $params ) );

            foreach ( $params as $param ) {
                @list( $name, $value ) = explode( ':', $param );
                $keyedParams[ $name ] = $value;
            }

            if ( preg_match('/^(\w+) (.*)$/',$route,$matches) ) {
                list( $IGNORE, $method, $route ) = $matches;
            }

            return new Specification(
                $route,
                $controller,
                $action ? $action : 'index',
                $keyedParams,
                $method
            );

        }

        return null;

    }

    /**
     * Parse a spec inside a controller block
     *
     * @param string $specText
     *
     * @return Specification
     */
    protected function parseControllerSpec( $controller, $specText, $baseurl ) {

        $specText = str_replace( '= ', "= $controller:", $specText );
        $specText = preg_replace( '/^(.*?)(\/.*)$/', '$1' . $baseurl . '$2', $specText );

        return $this->parseSpec( $specText );

    }

}
