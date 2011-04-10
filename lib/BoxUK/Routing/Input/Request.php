<?php

namespace BoxUK\Routing\Input;

/**
 * Interface for request classes implemented by the application
 *
 * @copyright Copyright (c) 2010, Box UK
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link http://github.com/boxuk/boxuk-routing
 * @since 1.0
 */
interface Request {

    const METHOD_GET = 'GET';

    const METHOD_POST = 'POST';

    const METHOD_DELETE = 'DELETE';

    const METHOD_PUT = 'PUT';
    
    /**
     * Returns the request method as specified by METHOD_* consts
     *
     * @return string
     */
    public function getMethod();

    /**
     * Sets a request variable value
     *
     * @param string $name
     * @param string $value
     */
    public function setValue( $name, $value );

    /**
     * Returns the value of a request variable, $default if it hasn't been specified
     *
     * @param string $name
     * @param mixed $default
     *
     * @return string
     */
    public function getValue( $name, $default=null );

}
