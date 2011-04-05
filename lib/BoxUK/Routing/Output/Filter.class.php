<?php

namespace BoxUK\Routing\Output;

/**
 * This filter is meant to be used in conjunction with the RequestRouter
 * request router to re-write urls it can process.
 *
 * @copyright Copyright (c) 2010, Box UK
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link http://github.com/boxuk/boxuk-routing
 * @since 1.0
 */
interface Filter {

    /**
     * Sets the name of the php script that acts as the front controller
     *
     * @param string $name
     */
    public function setFrontController( $name );

    /**
     * Looks for links in the markup that can be re-written to route-style urls
     * 
     * @param string $html
     */
    public function process( &$html );

}
