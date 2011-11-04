<?php

namespace BoxUK\Routing\Input;

require_once 'tests/php/bootstrap.php';

use BoxUK\Routing\Configuration,
    BoxUK\Routing\Specification,
    BoxUK\Routing\Specification\StandardParser,
    BoxUK\Routing\Config;

class StandardRouterTest extends \PHPUnit_Framework_TestCase {

    public function testMatchedRouteSpecificationIsReturnedWhenMatched() {
        $router = new MockRouter( new Config() );
        $router->setRoutes(array( '/ = default()') );
        $this->assertInstanceOf( 'BoxUK\Routing\Specification', $router->process(new TestRequest(),'/') );
    }
    
    public function testFalseIsReturnedWhenNoRouteIsMatched() {
        $router = new StandardRouter( new Config() );
        $this->assertFalse( $router->process(new TestRequest(),'') );
    }

    public function testRoutesPassedIntoTheInitMethodAreUsed() {
        $request = new TestRequest();
        $router = new StandardRouter( new Config() );
        $parser = new StandardParser();
        $router->init(array( $parser->parseSpec('/user/:num = user:show( id )') ), Specification::$types );
        $router->process( $request, '/user/123' );
        $this->assertEquals( 'user', $request->getValue('controller') );
        $this->assertEquals( 'show', $request->getValue('action') );
        $this->assertEquals( '123', $request->getValue('id') );
    }

    public function testExtensionToIgnoreCanBeSpecified() {
        $request = new TestRequest();
        $config = new Config();
        $config->setExtension( 'html' );
        $router = new StandardRouter( $config );
        $parser = new StandardParser();
        $router->init(array( $parser->parseSpec('/user/:num = user:show( id )') ), Specification::$types );
        $router->process( $request, '/user/123.html' );
        $this->assertEquals( 'user', $request->getValue('controller') );
        $this->assertEquals( 'show', $request->getValue('action') );
        $this->assertEquals( '123', $request->getValue('id') );
    }

    public function testSiteWebRootIsStrippedFromUrlsBeforeTheyreMatched() {
        $this->doTestRoute(
            '/sub/folder/user',
            '/user = user()',
            array(
                'controller' => 'user',
                'action' => 'index'
            ),
            null,
            '/sub/folder/'
        );
    }

    public function testSiteWebRootDoesntNeedToHaveATrailingSlash() {
        $this->doTestRoute(
            '/sub/folder/user',
            '/user = user()',
            array(
                'controller' => 'user',
                'action' => 'index'
            ),
            null,
            '/sub/folder'
        );
    }

    public function testRouteWithMethod() {
        $request = new TestRequest( 'DELETE' );
        $this->doTestRoute(
            '/user/123',
            array(
                'PUT /user/:num = user:update( id )',
                'DELETE /user/:num = user:delete( id )',
                '/user/:num = user:show( id )'
            ),
            array(
                'controller' => 'user',
                'action' => 'delete'
            ),
            $request
        );
    }

    public function testStartOfRouteIsMatchedWithSlash() {
        $this->doTestRoute(
            '/search/groups/2?q=lorem',
            array(
                '/groups/:word = group( action )',
                '/search/:word/:num = search:searchContent( filter, page )'
            ),
            array(
                'controller' => 'search',
                'action' => 'searchContent',
                'page' => '2',
                'filter' => 'groups'
            )
        );
    }

    public function testDefaultRouteMatched() {
        $this->doTestRoute(
            '/',
            array(
                '/ = group()',
            ),
            array(
                'controller' => 'group',
                'action' => 'index',
            )
        );
    }

    public function testDefaultRouteMatchedWithIndexDotPhp() {
        $this->doTestRoute(
            '/index.php',
            array(
                '/ = group()',
            ),
            array(
                'controller' => 'group',
                'action' => 'index',
            )
        );
    }


    public function testStartOfRouteIsMatched() {
        $this->doTestRoute(
            '/search/groups/2?q=lorem',
            array(
                '/groups/:word = group( action )',
                '/search/:word/:num = search:searchContent( filter, page )'
            ),
            array(
                'controller' => 'search',
                'action' => 'searchContent',
                'page' => '2',
                'filter' => 'groups'
            )
        );
    }

    public function testSpecialRegExpCharactersEscaped() {
        $this->doTestRoute(
            '/group/12/administration',
            array(
                '/group/:num.:word = group( id, format, action:show )',
                '/group/:num/:word = group( id, action )'
            ),
            array(
                'controller' => 'group',
                'action' => 'administration',
                'id' => '12'
            )
        );
    }

    public function testRouteToController() {
        $this->doTestRoute(
            '/user',
            '/user = user()',
            array(
                'controller' => 'user',
                'action' => 'index'
            )
        );
    }

    public function testLegacyRouteToController() {
        $this->doTestRoute(
            '/user',
            'user = user()',
            array(
                'controller' => 'user',
                'action' => 'index'
            )
        );
    }

    public function testRouteWithTrailingSlash() {
        $this->doTestRoute(
            '/user/',
            '/user = user()',
            array(
                'controller' => 'user',
                'action' => 'index'
            )
        );
    }

    public function testRouteWithAnyParam() {
        $this->doTestRoute(
            '/help/123/this-is-a%20title',
            '/help/:num/:any = help( id, title )',
            array(
                'controller' => 'help',
                'action' => 'index',
                'id' => '123',
                'title' => 'this-is-a title'
            )
        );
    }

    public function testMatchParamWhenUsedAsFileName() {
        $this->doTestRoute(
            '/rss/185.rss',
            '/rss/:num.rss = rss( blockId, format:rss )',
            array(
                'controller' => 'rss',
                'action' => 'index',
                'blockId' => '185',
                'format' => 'rss'
            )
        );
    }

    public function testRouteWithOnlyDefaults() {
        $this->doTestRoute(
            '/newsfeed.rss',
            '/newsfeed.rss = rss( blockId:185, format:rss )',
            array(
                'controller' => 'rss',
                'action' => 'index',
                'blockId' => '185',
                'format' => 'rss'
            )
        );
    }

    public function testRouteToControllerWithDefaultAction() {
        $this->doTestRoute(
            '/user',
            '/user = user:show()',
            array(
                'controller' => 'user',
                'action' => 'show'
            )
        );
    }

    public function testRouteWithParameterDefault() {
        $this->doTestRoute(
            '/my/groups',
            '/my/groups = group( currentTab:my )',
            array(
                'controller' => 'group',
                'action' => 'index',
                'currentTab' => 'my'
            )
        );
    }

    public function testRouteToControllerWithParameters() {
        $this->doTestRoute(
            '/user/25/edit',
            '/user/:num/:word = user( id, action )',
            array(
                'controller' => 'user',
                'action' => 'edit',
                'id' => 25
            )
        );
    }

    public function testFirstRouteOnlyMatched() {
        $this->doTestRoute(
            '/user/25',
            array(
                '/user/:num = user:show()',
                '/user/:num = user:delete()'
            ),
            array(
                'action' => 'show'
            )
        );
    }

    public function testRouteWithWordsMatches() {
        $this->doTestRoute(
            '/group/25/discussions/50',
            '/group/:num/discussions/:num = group( id, discussionId )',
            array(
                'id' => '25',
                'action' => 'index',
                'discussionId' => '50'
            )
        );
    }

    public function testDifferentControllerToFirstWord() {
        $this->doTestRoute(
            '/message/181',
            '/message/:num = usermessage:message( id )',
            array(
                'controller' => 'usermessage',
                'action' => 'message',
                'id' => '181'
            )
        );
    }

    public function testMatchStopsAtEndOfParameter() {
        $aUrls = array(
            '/my/messages/composeDraft',
            '/my/messages/composeDraft?foo=bar'
        );
        foreach ( $aUrls as $url ) {
            $this->doTestRoute(
                $url,
                array(
                    '/my/messages/compose = usermessage:composeMessage()',
                    '/my/messages/:word = usermessage( action )'
                ),
                array(
                    'controller' => 'usermessage',
                    'action' => 'composeDraft'
                )
            );
        }
    }

    public function testAnyParamDoesntIncludeSlashes() {
        $this->doTestRoute(
            '/forum/1/My+First+Topic/newDiscussion',
            array(
                '/forum/:num/:any = forum:showTopic( topicId, topicTitle )',
                '/forum/:num/:any/:word = forum( topicId, topicTitle, action )',
            ),
            array(
                'controller' => 'forum',
                'action' => 'newDiscussion',
                'topicId' => '1',
                'topicTitle' => 'My First Topic',
            )
        );
    }

    public function testAnyParamCanUseDots() {
        $this->doTestRoute(
            '/forum/1/My+First+Topic.htm',
            array(
                '/forum/:num/:any = forum:showTopic( topicId, topicTitle )',
            ),
            array(
                'controller' => 'forum',
                'action' => 'showTopic',
                'topicId' => '1',
                'topicTitle' => 'My First Topic.htm',
            )
        );
    }

    public function testUnmatchedWebRootIsNotIncorrectlyMatched() {
        $this->doTestRoute(
            '/user/1',
            array( '/user/:num = user( id )' ),
            array( 'controller' => 'user' ),
            null,
            '/foo'
        );
    }

    public function testCustomTypesAreParsedIntoParametersInTheRequestObject() {
        $parser = new StandardParser();
        list( $routes, $types ) = $parser->parseFile( 'tests/resources/routes-types.spec' );
        $router = new MockRouter( new Config() );
        $router->init( $routes, $types );
        $request = new TestRequest();
        $router->process( $request, '/user/3abc' );
        $this->assertEquals( '3abc', $request->getValue('id') );
    }

    private function doTestRoute( $url, $route, $aParams, $request=null, $siteWebRoot=null ) {
        $request = $request ? $request : $this->getRequest();
        $config = new Config();
        $config->setSiteWebRoot( $siteWebRoot ? $siteWebRoot : '' );
        $router = new MockRouter( $config );
        $router->setRoutes( is_array($route) ? $route : array( $route ) );
        $this->assertNotNull( $router->process($request,$url) );
        foreach ( $aParams as $name => $value ) {
            $this->assertEquals( $value, $request->getValue($name) );
        }
    }
    
    private function getRequest() {
        return new TestRequest();
    }

}

class MockRouter extends StandardRouter {

    public function setRoutes( $aRoutes, $routeTypes=null ) {
        $parser = new StandardParser();
        $aoRouteSpecs = array();
        foreach ( $aRoutes as $route ) {
            if ( $oRouteSpec = $parser->parseSpec($route) ) {
                $aoRouteSpecs[] = $oRouteSpec;
            }
        }
        $this->init( $aoRouteSpecs, $routeTypes ? $routeTypes : Specification::$types );
    }


}
