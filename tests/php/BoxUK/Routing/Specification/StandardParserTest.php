<?php

namespace BoxUK\Routing\Specification;

require_once 'tests/php/bootstrap.php';

class StandardParserTest extends \PHPUnit_Framework_TestCase {

    private $parser, $routeBlocksPath;

    public function setUp() {
        $this->parser = new StandardParser();
        $this->routeBlocksPath = __DIR__ . '/../../../../resources/route-blocks.spec';
        $this->routeBlocksPath2 = __DIR__ . '/../../../../resources/routes-blocks2.spec';
        $this->routeBaseUrlsPath = __DIR__ . '/../../../../resources/routes-baseurls.spec';
    }

    public function testParseBasicSpec() {
        $parser = new StandardParser();
        $spec = $parser->parseSpec( '/message/:num = usermessage( id )' );
        $this->assertNotNull( $spec );
        $this->assertEquals( $spec->getRoute(), '/message/:num' );
        $this->assertEquals( $spec->getController(), 'usermessage' );
        $this->assertEquals( $spec->getAction(), 'index' );
        $this->assertEquals( $spec->getParameters(), array( 'id' => '' ) );
    }

    public function testParsingLegacySpecDoesntReturnSlashPrefix() {
        $parser = new StandardParser();
        $spec = $parser->parseSpec( 'message/:num = usermessage( id )' );
        $this->assertEquals( $spec->getRoute(), 'message/:num' );
    }
    
    public function testParseSpecWithAction() {
        $parser = new StandardParser();
        $spec = $parser->parseSpec( '/message/:num = usermessage:show( id )' );
        $this->assertEquals( $spec->getAction(), 'show' );
    }

    public function testParseSpecWithMethod() {
        $parser = new StandardParser();
        $spec = $parser->parseSpec( 'DELETE /message/:num = usermessage:show( id )' );
        $this->assertEquals( $spec->getMethod(), 'DELETE' );
    }

    public function testParseSpecWithParameterDefaults() {
        $parser = new StandardParser();
        $spec = $parser->parseSpec( '/message/:num = usermessage:show( id, foo:bar )' );
        $aParams = $spec->getParameters();
        $this->assertEquals( $aParams['foo'], 'bar' );
    }

    public function testParsingWithSlashPrefix() {
        $parser = new StandardParser();
        $spec = $parser->parseSpec( '/message/:num = usermessage:show( id )' );
        $this->assertEquals( 'usermessage', $spec->getController() );
        $this->assertEquals( 'show', $spec->getAction() );
    }
    
    public function testParsingTheDefaultRoute() {
        $parser = new StandardParser();
        $spec = $parser->parseSpec( '/ = category()' );
        $this->assertEquals( 'category', $spec->getController() );
        $this->assertEquals( 'index', $spec->getAction() );
    }

    public function testParsingFileWithWindowsLineEndings() {
        $parser = new StandardParser();
        list( $routeSpecs ) = $parser->parseFile( __DIR__ . '/../../../../resources/windows_routes.spec');
        $this->assertEquals( count($routeSpecs), 2 );
    }

    public function testDefaultTypesAreReturnedWhenParsingAFile() {
        $parser = new StandardParser();
        list( $routeSpecs, $routeTypes ) = $parser->parseFile( __DIR__ . '/../../../../resources/routes.spec');
        $this->assertEquals( count($routeTypes), 4 ); // :num, :word, :file and :any
    }

    public function testExtraTypesSpecifiedInSpecFileAreReturnedWhenParsingFile() {
        $parser = new StandardParser();
        list( $routeSpecs, $routeTypes ) = $parser->parseFile( __DIR__ . '/../../../../resources/routes-types.spec');
        $this->assertEquals( $routeTypes['userid'], '\d\w+' );
    }

    public function testRoutesInsideControllerBlocksDontNeedToSpecifyTheController() {
        list( $routeSpecs, $routeTypes ) = $this->parser->parseFile( $this->routeBlocksPath );
        $this->assertEquals( 4, count($routeSpecs) );
    }

    public function testRoutesInsideControllerBlocksCanSpecifyTheAction() {
        list( $routeSpecs, $routeTypes ) = $this->parser->parseFile( $this->routeBlocksPath );
        $this->assertEquals( $routeSpecs[2]->getAction(), 'blah' );
    }

    public function testRoutesInsideControllerBlocksAssumeIndexActionIfNoneSpecified() {
        list( $routeSpecs, $routeTypes ) = $this->parser->parseFile( $this->routeBlocksPath );
        $this->assertEquals( $routeSpecs[1]->getAction(), 'index' );
    }

    public function testControllerBlocksCanBeEndedUsingAStarInSquareBrackets() {
        list( $routeSpecs, $routeTypes ) = $this->parser->parseFile( $this->routeBlocksPath );
        $this->assertEquals( 4, count($routeSpecs) );
        $this->assertEquals( $routeSpecs[3]->getController(), 'foo' );
        $this->assertEquals( $routeSpecs[3]->getAction(), 'bar' );
    }

    public function testControllerBlocksCanOptionallySpecifyBaseUrls() {
        list( $routeSpecs, $routeTypes ) = $this->parser->parseFile( $this->routeBaseUrlsPath );
        $this->assertEquals( 3, count($routeSpecs) );
        $this->assertEquals( '/base/path/:word', $routeSpecs[0]->getRoute() );
    }

    public function testRoutesDefinedAfterControllerBlocksWithBaseUrlsHaveTheCorrectRoute() {
        list( $routeSpecs, $routeTypes ) = $this->parser->parseFile( $this->routeBaseUrlsPath );
        $this->assertEquals( '/foo', $routeSpecs[2]->getRoute() );
    }

    public function testControllerBlockRoutesHaveAllTheirBitsParsedCorrectly() {
        list( $routeSpecs, $routeTypes ) = $this->parser->parseFile( $this->routeBlocksPath2 );
        $this->assertEquals( '/content/:word/:num', $routeSpecs[0]->getRoute() );
    }

}
