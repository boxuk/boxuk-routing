<?php

namespace BoxUK\Routing;

use BoxUK\Routing\Specification\StandardParser;

require_once 'tests/php/bootstrap.php';

class StandardRewriterTest extends \PHPUnit_Framework_TestCase {

    public function testOriginalUrlIsReturnedWhenNoRoutesAreMatchedForRewriting() {
        $url = 'server.php?controller=this&action=that';
        $this->doTest( array(), array( $url => $url ) );
    }

    public function testDashesCanBeHandledInParameters() {
        $this->doTest(
            array( '/category/:any = category:showCategory( contentId )' ),
            array( 'server.php?controller=category&action=showCategory&contentId=some-value' => '/category/some-value' )
        );
    }

    public function testWithEmptyParameterPair() {
        $this->doTest(
            array( 'rss/news/:num.rss = rss( blockId )' ),
            array( 'server.php?controller=rss&&blockId=185' => '/rss/news/185.rss' )
        );
    }

    public function testUsingFileExtension() {
        $this->doTest(
            array( 'rss/news/:num.rss = rss( blockId )' ),
            array( 'server.php?controller=rss&blockId=185' => '/rss/news/185.rss' )
        );
    }

    public function testUrlWithSpecialCharacters() {
        $this->doTest(
            array( '/search = search:searchContent()' ),
            array(
                'server.php?controller=search&action=searchContent&q=&quot;' => '/search?q="',
                'server.php?controller=search&action=searchContent&q=&lt;' => '/search?q=<'
            )
        );
    }

    public function testRewriteUrlWithSiteWebRoot() {
        $this->doTest(
            array( 'user/:num = user:show( id )' ),
            array( 'server.php?controller=user&action=show&id=123' => '/user/123' )
        );
    }

    public function testBlankParamsNotIncludedInMatch() {
        $this->doTest(
            array(
                'askanexpert/:num/:word/:num = askanexpert( id, action, page )', // should NOT match as id doesn't have a value
                'askanexpert/:num = askanexpert( page )'
            ),
            array( 'server.php?controller=askanexpert&action=index&id=&page=3' => '/askanexpert/3' )
        );
    }

    public function testRouteWithParameterDefault() {
        $this->doTest(
            array( 'my/groups = group( currentTab:my )', 'groups/:word = group( currentTab )' ),
            array(
                'server.php?controller=group&currentTab=my' => '/my/groups',
                'server.php?controller=group&currentTab=newest' => '/groups/newest'
            )
        );
    }

    public function testWithMultipleRoutes() {
        $this->doTest(
            array( 'groups = group:index()', 'settings = usersettings:index()' ),
            array( 'server.php?controller=usersettings' => '/settings' )
        );
    }

    public function testUnmatchedArgsAddedToQueryString1() {
        $this->doTest(
            array( 'message/:num = usermessage:message( id )' ),
            array( 'server.php?controller=usermessage&action=message&id=123&format=lightbox' => '/message/123?format=lightbox' )
        );
    }

    public function testWithEncodedAmps() {
        $this->doTest(
            array( 'profile/:word = userprofile:index( action )', 'profile = userprofile:index()' ),
            array( '/~rod/bcsmn/site/server.php?controller=userprofile&amp;action=preview' => '/profile/preview' )
        );
    }

    public function testActionAddedIfNotDefault() {
        $this->doTest(
            array( 'profile = userprofile:index()' ),
            array( 'server.php?controller=userprofile&action=preview' => '/profile?action=preview' )
        );
    }

    public function testControllerMatchNotCaseSensitive() {
        $this->doTest(
            array( 'profile = userprofile:index()' ),
            array( 'server.php?controller=UserProfile&action=preview' => '/profile?action=preview' )
        );
    }

    public function testActionMatchedWhenDefaultSpecified() {
        $this->doTest(
            array(
                'messaging/compose = usermessage:composeMessage()',
                'messaging/:word = usermessage( action )',
                'messaging = usermessage()'
            ),
            array( 'server.php?controller=usermessage' => '/messaging' )
        );
    }

    public function testDefaultActionUsedWhenSpecified() {
        $this->doTest(
            array(
                'messaging/compose = usermessage:composeMessage()',
                'messaging/:word = usermessage( action )',
                'messaging = usermessage()'
            ),
            array( 'server.php?controller=usermessage&action=sent' => '/messaging/sent' )
        );
    }

    public function testLinkFragmentsPreserved() {
        $this->doTest(
            array( 'messaging = usermessage()' ),
            array( 'server.php?controller=usermessage#inbox' => '/messaging#inbox' )
        );
    }

    public function testParameterWithCommer() {
        $this->doTest(
            array( 'my/messages/:word = usermessage( action )' ),
            array( 'server.php?controller=usermessage&action=multipleMarkAsNotTrash&id=6,52&postAction=sent' => '/my/messages/multipleMarkAsNotTrash?id=6,52&postAction=sent' )
        );
    }

    public function testAnyParamCanBeAFile() {
        $this->doTest(
            array( '/group/:num/:any = group:show( id, title )' ),
            array( 'server.php?controller=group&action=show&id=123&title=My%20Group.htm' => '/group/123/My%20Group.htm' )
        );
    }

    public function testPlussesCanBeIncludedAnyAnyParam() {
        $this->doTest(
            array( '/group/:num/:any = group:show( id, title )' ),
            array( 'server.php?controller=group&action=show&id=123&title=My+Group.htm' => '/group/123/My+Group.htm' )
        );
    }

    public function testRewriteUrlWithUrlInParameter() {
        $this->doTest(
            array( 'user/:num = user:show( id )' ),
            array( 'server.php?controller=user&action=show&id=123&url=https%3A%2F%2Frouting.com%2Frouting%2Froute.php?ID%3D1' => '/user/123?url=https%3A%2F%2Frouting.com%2Frouting%2Froute.php?ID%3D1' )
        );
    }
 
    public function testCustomTypesAreUsedWhenRewritingRoutes() {
        $types = Specification::$types;
        $types[ 'userid' ] = '\d\w+';
        $this->doTest(
            array( '/user/:userid = user:show( id )' ),
            array( 'server.php?controller=user&action=show&id=1abc' => '/user/1abc' ),
            $types
        );
    }

    public function testExtensionCanBeAddedToRewrittenUrls() {
        $this->doTest(
            array( '/category/:any = category:showCategory( contentId )' ),
            array( 'server.php?controller=category&action=showCategory&contentId=some-value' => '/category/some-value.html' ),
            array(),
            'html'
        );
    }

    public function testSiteRootPrepended() {
        $this->doTest(
            array( 'profile = userprofile:index()' ),
            array( 'server.php?controller=userprofile&action=preview' => '/some/dir/profile?action=preview' ),
            array(),
            false,
            '',
            '/some/dir/'
        );
    }

    public function testShouldCheckForFullDomain() {
        $this->doTest(
            array( 'messaging/:word = usermessage( action )' ),
            array( 'server.php?controller=usermessage&action=sent' => 'http://smynx.com/messaging/sent' ),
            array(),
            false,
            'smynx.com'
        );
    }

    public function testUrlNotRewrittenWhenADomainIsSetAndTheUrlDoesntMatchIt() {
        $url = 'http://boxuk.com/server.php?controller=usermessage&action=sent';
        $this->doTest(
            array( 'messaging/:word = usermessage( action )' ),
            array( $url => $url ),
            array(),
            false,
            'smynx.com'
        );
    }

    /**
     * Tests some specs against some URLs
     *
     * @param array $specs
     * @param array $asserts
     * @param array $types
     * @param string $extension
     * @param string $domain
     * @param string $webRoot
     */
    protected function doTest( array $specs, array $asserts, array $types = array(), $extension=false, $domain='', $webRoot='/' ) {

        if ( empty($types) ) {
            $types = Specification::$types;
        }

        $routeSpecs = array();
        $parser = new StandardParser();
        $rewriter = new StandardRewriter();
        $includeDomain = ( $domain );

        if ( $extension ) {
            $rewriter->setExtension( $extension );
        }

        foreach ( $specs as $spec ) {
            $routeSpecs[] = $parser->parseSpec( $spec );
        }

        $rewriter->init( $routeSpecs, $types, $domain, $webRoot );

        foreach ( $asserts as $url => $expected ) {
            $this->assertEquals( $expected, $rewriter->rewrite($url,$includeDomain) );
        }

    }

}
