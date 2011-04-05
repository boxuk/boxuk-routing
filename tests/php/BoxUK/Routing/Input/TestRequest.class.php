<?php

namespace BoxUK\Routing\Input;

/**
 * A simple implementation of the Request interface for use in tests
 * 
 */
class TestRequest implements Request {

    private $method, $params;

    public function __construct( $method='GET' ) {
        $this->method = $method;
        $this->params = array();
    }

    public function getMethod() {
        return $this->method;
    }

    public function setValue( $name, $value ) {
        $this->params[ $name ] = $value;
    }

    public function getValue( $name, $default=null ) {
        return isset( $this->params[$name] )
            ? $this->params[ $name ]
            : $default;
    }

}
