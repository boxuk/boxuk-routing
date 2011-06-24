<?php

namespace BoxUK\Routing;

/**
 * A route specification
 *
 * @copyright Copyright (c) 2010, Box UK
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link http://github.com/boxuk/boxuk-routing
 * @since 1.0
 */
class Specification {

    /**
     * Default request method (blank means all HTTP verbs accepted)
     */
    const DEFAULT_METHOD = '';

    /**
     * Regexp for :num keyword
     */
    const NUM = '(\d+)';

    /**
     * Regexp for :word keyword
     */
    const WORD = '(\w+)';

    /**
     * Regexp for :any keyword
     */
    const ANY = '([^\/]+)';

    /**
     * Regexp for :any keyword
     */
    const FILE = '([^\/]+\.\w+)';

    /**
     * @var array Default mapping of type names to regexps
     */
    public static $types = array(
        'num' => self::NUM,
        'word' => self::WORD,
        'any' => self::ANY,
        'file' => self::FILE
    );

    /**
     * @var string The route description
     */
    private $route;

    /**
     * @var string Controller to handle route
     */
    private $controller;

    /**
     * @var string Default action for the route
     */
    private $action;

    /**
     * @var array Array of parameters for the route
     */
    private $params;

    /**
     * @var string The request method for the route (blank means all)
     */
    private $method;

    /**
     * Creates a new route specification
     *
     * @param string $route
     * @param string $controller
     * @param string $action
     * @param string $method BoxUK\Input\Request::METHOD_*
     */
    public function __construct( $route, $controller, $action, array $params, $method=self::DEFAULT_METHOD ) {
        
        $this->route = $route;
        $this->controller = $controller;
        $this->action = $action;
        $this->params = $params;
        $this->method = $method;

    }

    /**
     * Returns the route description
     *
     * @return string
     */
    public function getRoute() {
        
        return $this->route;
        
    }

    /**
     * Returns the controller to handle the route
     *
     * @return string
     */
    public function getController() {
        
        return $this->controller;
        
    }

    /**
     * Returns the default action for the route
     *
     * @return string
     */
    public function getAction() {
        
        return $this->action;
        
    }

    /**
     * Returns the parameters for this route
     *
     * @return array
     */
    public function getParameters() {
        
        return $this->params;
        
    }

    /**
     * Returns the request method for the route
     * 
     * @return string
     */
    public function getMethod() {
        
        return $this->method;
        
    }

}
