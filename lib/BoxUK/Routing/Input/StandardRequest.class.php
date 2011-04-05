<?php

namespace BoxUK\Routing\Input;

class StandardRequest implements Request {

    /**
     * Returns the request method as specified by METHOD_* consts
     *
     * @return string
     */
    public function getMethod() {
        
        return $_SERVER[ 'REQUEST_METHOD' ];
        
    }

    /**
     * Sets a request variable value
     *
     * @param string $name
     * @param string $value
     */
    public function setValue( $name, $value ) {

        $_REQUEST[ $name ] = $value;

    }

    /**
     * Returns the value of a request variable, $default if it hasn't been specified
     *
     * @param string $name
     * @param mixed $default
     *
     * @return string
     */
    public function getValue( $name, $default=null ) {

        return isset( $_REQUEST[$name] )
            ? $_REQUEST[ $name ]
            : $default;

    }

}
