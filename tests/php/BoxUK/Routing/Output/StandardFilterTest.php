<?php

namespace BoxUK\Routing\Output;

use BoxUK\Routing\Input\TestRequest,
    BoxUK\Routing\StandardRewriter,
    BoxUK\Routing\RouteSpecification,
    BoxUK\Routing\Specification,
    BoxUK\Routing\Specification\StandardParser,
    BoxUK\Routing\Config;

require_once 'tests/php/bootstrap.php';

class StandardFilterTest extends \PHPUnit_Framework_TestCase {

    public function testFrontControllerScriptNameDefaultsToServer() {
        $html = '<a href="server.php?controller=usermessage&action=message&id=123">link</a>';
        $filter = $this->getFilter(array( 'message/:num = usermessage:message( id )' ));
        $filter->process( $html );
        $this->assertEquals( '<a href="/message/123">link</a>', $html );
    }
    
    public function testFrontControllerScriptCanBeSetUsingSetfrontcontroller() {
        $html = '<a href="front.php?controller=usermessage&action=message&id=123">link</a>';
        $filter = $this->getFilter(array( 'message/:num = usermessage:message( id )' ));
        $filter->setFrontController( 'front' );
        $filter->process( $html );
        $this->assertEquals( '<a href="/message/123">link</a>', $html );
    }

    public function testRewriteHiddenFieldsWithUrlEncodableCharacters() {
        $html = <<<EOT
<form action="server.php">
    <input type="hidden" name="controller" value="category" />
    <input type="hidden" name="action" value="showCategory" />
    <input type="hidden" name="contentId" value="12184" />
    <input type="hidden" name="Module[4926][action]" value="sendInvite" />
</form>
EOT;
        $filter = $this->getFilter(array(
            'category/:num = category:showCategory( contentId )'
        ));
        $filter->process( $html );
        $this->assertContains( 'action="/category/12184"', $html );
        $this->assertContains( 'name="Module[4926][action]"', $html );
    }

        public function testRewriteHiddenFieldsWithUrlEncodableCharactersInMultipleForms() {
        $html = <<<EOT
<form action="server.php">
    <input type="hidden" name="controller" value="category" />
    <input type="hidden" name="action" value="showCategory" />
    <input type="hidden" name="contentId" value="1" />
    <input type="hidden" name="Module[1][action]" value="sendInvite" />
</form>

 <form action="server.php">
    <input type="hidden" name="controller" value="category" />
    <input type="hidden" name="action" value="showCategory" />
    <input type="hidden" name="contentId" value="2" />
    <input type="hidden" name="Module[2][action]" value="sendInvite" />
</form>
EOT;
        $filter = $this->getFilter(array(
            'category/:num = category:showCategory( contentId )'
        ));
        $filter->process( $html );
        $this->assertContains( 'action="/category/1"', $html );
        $this->assertContains( 'name="Module[1][action]"', $html );
        $this->assertContains( 'action="/category/2"', $html );
        $this->assertContains( 'name="Module[2][action]"', $html );
    }

    public function testRewritingLinks() {
        $html = '<a href="server.php?controller=usermessage&action=message&id=123">link</a>';
        $filter = $this->getFilter(array(
            'message/:num = usermessage:message( id )'
        ));
        $filter->process( $html );
        $this->assertEquals( '<a href="/message/123">link</a>', $html );
    }

    public function testRewriteFormAction() {
        $html = '<form action="server.php">' . "\n" .
                    '<input type="hidden" name="controller" value="userprofile" />' . "\n" .
                    '<input type="hidden" name="action" value="preview" />' . "\n" .
                    '<input type="hidden" name="foo" value="bar" />' .
                '</form>';
        $filter = $this->getFilter(array(
            'profile/:word = userprofile( action )'
        ));
        $filter->process( $html );
        $this->assertContains( 'action="/profile/preview"', $html );
        $this->assertContains( 'name="foo"', $html );
        $this->assertContains( 'value="bar"', $html );
    }

    public function testRewriteFormActionWithMethod() {
        $html = '<form action="server.php">' . "\n" .
                    '<input type="hidden" name="controller" value="user" />' . "\n" .
                    '<input type="hidden" name="action" value="delete" />' . "\n" .
                    '<input type="hidden" name="id" value="123" />' .
                '</form>';
        $filter = $this->getFilter(array(
            'PUT user/:num = user:update( id )',
            'DELETE user/:num = user:delete( id )',
            'user/:num = users:show( id )'
        ));
        $filter->process( $html );
        $this->assertContains( 'action="/user/123"', $html );
        $this->assertContains( 'name="__method"', $html );
        $this->assertContains( 'value="DELETE"', $html );
    }

    public function testRewriteFormActionWithWebRoot() {
        $html = '<form action="/site/root/server.php">' . "\n" .
                    '<input type="hidden" name="controller" value="userprofile" />' . "\n" .
                    '<input type="hidden" name="action" value="preview" />' . "\n" .
                    '<input type="hidden" name="foo" value="bar" />' .
                '</form>';
        $filter = $this->getFilter(array(
            'profile/:word = userprofile( action )'
        ));
        $filter->process( $html );
        $this->assertContains( 'action="/profile/preview"', $html );
        $this->assertContains( 'name="foo"', $html );
        $this->assertContains( 'value="bar"', $html );
    }

    public function testRewriteUnmatchedFormNotChanged() {
        $orig = '<form action="server.php">' . "\n" .
                    '<input type="hidden" name="controller" value="userprofile" />' . "\n" .
                    '<input type="hidden" name="action" value="preview" />' . "\n" .
                    '<input type="hidden" name="foo" value="bar" />' .
                '</form>';
        $html = $orig;
        $filter = $this->getFilter(array(
            'foo/:num = group( id )'
        ));
        $filter->process( $html );
        $this->assertEquals( $orig, $html );
    }

    public function testRewriteFormInputValue() {
        $html = '<input value="server.php?controller=userprofile&action=preview&foo=bar">';
        $filter = $this->getFilter(array(
            'profile/:word = userprofile( action )'
        ));
        $filter->process( $html );
        $this->assertEquals( '<input value="/profile/preview?foo=bar">', $html );
    }

    public function testLinksInRss() {
        $elements = array( 'link', 'guid' );
        $filter = $this->getFilter(array(
            'profile/:word = userprofile( action )'
        ), 'scott.com', '/' );
        foreach ( $elements as $elem ) {
            $html1 = sprintf( '<%1$s>server.php?controller=userprofile&action=preview&foo=bar</%1$s>', $elem );
            $filter->process( $html1 );
            $this->assertEquals( sprintf('<%1$s>http://scott.com/profile/preview?foo=bar</%1$s>',$elem), $html1 );
        }
    }

    public function getFilter( $specs, $siteDomain='', $siteWebRoot='/', $oReq=null ) {
        $parser = new StandardParser();
        $routeSpecs = array();
        foreach ( $specs as $spec ) {
            $routeSpecs[] = $parser->parseSpec( $spec );
        }
        $config = new Config();
        $config->setSiteDomain( $siteDomain );
        $rewriter = new StandardRewriter( $config );
        $rewriter->init( $routeSpecs, Specification::$types );
        return new StandardFilter(
            $rewriter,
            $oReq ? $oReq : new TestRequest()
        );
    }

    public function testDoesNotRewriteFormActionsWithJustAnchorUrls() {
        $html = '<form action="#anchor">' .
                    '<input type="hidden" name="controller" value="userprofile" />' .
                    '<input type="hidden" name="action" value="preview" />' .
                '</form>';

        $filter = $this->getFilter( array(
            'profile/:word = userprofile( action )'
        ), 'boxuk.com', '/' );

        $filter->process( $html );
        
        $this->assertContains( ' action="#anchor"', $html );
    }

}

class TestFilter extends StandardFilter {

    private $routeSpecs;

    public function __construct( $oSite = null ) {
        parent::__construct( $oSite, null );
        $this->aoRouteSpecs = array();
    }

    public function addRoute( $oRouteSpec ) {
        $this->aoRouteSpecs[] = $oRouteSpec;
    }

    public function getRouteSpecs() {
        return $this->aoRouteSpecs;
    }

}
