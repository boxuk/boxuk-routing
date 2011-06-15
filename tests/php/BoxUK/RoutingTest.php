<?php

namespace BoxUK;

require_once 'tests/php/bootstrap.php';

class RouterTest extends \PHPUnit_Framework_TestCase {

    private $config, $routing, $routesFile, $req;
    
    public function setUp() {
        $this->config = new Routing\Configuration;
        $this->routing = new Routing( $this->config );
        $this->routesFile = getcwd() . '/tests/resources/routes.spec';
        $this->req = new \BoxUK\Routing\Input\StandardRequest();
    }

    public function testRouterCanBeCreatedWithHelper() {
        $this->assertInstanceOf( 'BoxUK\Routing\Input\Router', $this->routing->getRouter() );
    }

    public function testStandardRouterIsReturnedByDefault() {
        $this->assertInstanceOf( 'BoxUK\Routing\Input\StandardRouter', $this->routing->getRouter() );
    }

    public function testFilterCanBeCreatedWithHelper() {
        $this->assertInstanceOf( 'BoxUK\Routing\Output\Filter', $this->routing->getFilter() );
    }

    public function testStandardFilterReturnedByDefault() {
        $this->assertInstanceOf( 'BoxUK\Routing\Output\StandardFilter', $this->routing->getFilter() );
    }

    public function testRewriterCanBeCreatedWithHelper() {
        $this->assertInstanceOf( 'BoxUK\Routing\Rewriter', $this->routing->getRewriter() );
    }

    public function testStandardRewriterReturnedByDefault() {
        $this->assertInstanceOf( 'BoxUK\Routing\StandardRewriter', $this->routing->getRewriter() );
    }

    public function testSiteWebRootDefaultsToASlash() {
        $this->config->setRoutesFile( $this->routesFile );;
        $url = 'server.php?controller=user&action=show&id=123';
        $rewriter = $this->routing->getRewriter();
        $this->assertEquals( '/user/123', $rewriter->rewrite($url) );
    }

    public function testRoutesFileCanBeSpecifiedWithHelperAndIsUsedByAllObjects() {
        $this->config->setRoutesFile( $this->routesFile );
        // router
        $router = $this->routing->getRouter();
        $route = $router->process( $this->req, '/user/123' );
        $this->assertEquals( 'user/:num', $route->getRoute() );
        // filter
        $html = '<a href="server.php?controller=user&action=show&id=123">link</a>';
        $filter = $this->routing->getFilter();
        $filter->process( $html );
        $this->assertEquals( '<a href="/user/123">link</a>', $html );
        // rewriter
        $url = 'server.php?controller=user&action=show&id=123';
        $rewriter = $this->routing->getRewriter();
        $this->assertEquals( '/user/123', $rewriter->rewrite($url) );
    }

    public function testExtensionCanBeSpecifiedAndIsUsedByAllObjects() {
        $this->config->setRoutesFile( $this->routesFile );
        $this->config->setExtension( 'html' );
        // router
        $router = $this->routing->getRouter();
        $route = $router->process( $this->req, '/user/123.html' );
        $this->assertEquals( 'user/:num', $route->getRoute() );
        // filter
        $html = '<a href="server.php?controller=user&action=show&id=123">link</a>';
        $filter = $this->routing->getFilter();
        $filter->process( $html );
        $this->assertEquals( '<a href="/user/123.html">link</a>', $html );
        // rewriter
        $url = 'server.php?controller=user&action=show&id=123';
        $rewriter = $this->routing->getRewriter();
        $this->assertEquals( '/user/123.html', $rewriter->rewrite($url) );
    }

    public function testSiteDomainCanBeSpecifiedAndIsUsedWithRewriterWhenRequested() {
        $this->config->setRoutesFile( $this->routesFile );
        $this->config->setSiteDomain( 'mysite.com' );
        // rewriter
        $url = 'server.php?controller=user&action=show&id=123';
        $rewriter = $this->routing->getRewriter();
        $this->assertEquals( 'http://mysite.com/user/123', $rewriter->rewrite($url,true) );
    }

    public function testSiteWebRootCanBeSpecifiedAndIsUsedWithAllObjects() {
        $this->config->setRoutesFile( $this->routesFile );
        $this->config->setSiteWebRoot( '/sub/folder/' );
        // filter
        $html = '<a href="server.php?controller=user&action=show&id=123">link</a>';
        $filter = $this->routing->getFilter();
        $filter->process( $html );
        $this->assertEquals( '<a href="/sub/folder/user/123">link</a>', $html );
        // rewriter
        $url = 'server.php?controller=user&action=show&id=123';
        $rewriter = $this->routing->getRewriter();
        $this->assertEquals( '/sub/folder/user/123', $rewriter->rewrite($url) );
        // router
        $router = $this->routing->getRouter();
        $route = $router->process( $this->req, '/sub/folder/user/123' );
        $this->assertEquals( 'user/:num', $route->getRoute() );
    }

}
