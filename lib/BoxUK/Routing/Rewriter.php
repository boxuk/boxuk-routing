<?php

namespace BoxUK\Routing;

/**
 * Interface for rewriters
 *
 * @copyright Copyright (c) 2010, Box UK
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link http://github.com/boxuk/boxuk-routing
 * @since 1.0
 */
interface Rewriter {

    /**
     * Tries to rewrite the specified URL using configured routes, optionally
     * including the domain.  The new URL is returned if it is rewritten, or
     * the old url is returned otherwise.
     *
     * @param string $url
     * @param bool $includeDomain (false)
     *
     * @return string
     */
    public function rewrite( $url, $includeDomain=false );

    /**
     * Tries to find rewrite information for a url, returns null if it can't
     * match anything.  The info returned is...
     *
     * array(
     *     'domain.com',            // domain
     *     '/site/web/root/',       // web root
     *     'user/login',            // url part
     *     '?foo=bar#fragment',     // query string
     *     $specification           // route spec matched
     * )
     *
     * @param string $url
     *
     * @return array
     */
    public function getRewriteInfo( $url );

}
