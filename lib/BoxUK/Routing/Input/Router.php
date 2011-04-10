<?php

namespace BoxUK\Routing\Input;

use BoxUK\Routing\Specification;

/**
 * Class to process defined routes and extract information to update the Request
 * object with.
 * 
 * @copyright Copyright (c) 2010, Box UK
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link http://github.com/boxuk/boxuk-routing
 * @since 1.0
 */
interface Router {

    /**
     * Runs the router on any configured route specifications.  Returns the matched
     * route object if there was one, false otherwise.
     *
     * @param Request $request
     * @param string $url
     *
     * @return Specification
     */
    public function process( Request $request, $url );

    /**
     * Returns an array of configured route specs
     *
     * @return array
     */
    public function getRouteSpecs();

    /**
     * Sets an extension URLs are allowed to have and still be matched
     *
     * @param string $extension
     */
    public function setExtension( $extension );
    
}
