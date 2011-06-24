<?php

namespace BoxUK\Routing\Specification;

/**
 * Interface for specification parsers
 *
 * @copyright Copyright (c) 2010, Box UK
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link http://github.com/boxuk/boxuk-routing
 * @since 0.1.7
 */
interface Parser {

    /**
     * Parses a routes file to extract all the route specifications
     *
     * @param string $path
     * 
     * @return array
     */
    public function parseFile( $path );

    /**
     * Parse a single route specification
     *
     * @param string $spec eg. '/foo/:num = ctrl()'
     * 
     * @return BoxUK\Routing\Specification
     */
    public function parseSpec( $spec );

}
