<?php

namespace BoxUK\Routing\Input;

class StandardRequestTest extends \PHPUnit_Framework_TestCase {

    private $request;

    public function setUp() {
        $this->request = new StandardRequest();
    }

    public function testStandardRequestImplementsTheRequestInterface() {
        $this->assertInstanceOf( 'BoxUK\Routing\Input\Request', $this->request );
    }

    public function testSettingAValuePutsItInRequestSuperglobal() {
        $value = time() * time();
        $this->request->setValue( 'foo', $value );
        $this->assertEquals( $_REQUEST['foo'], $value );
    }

    public function testGettingAValueReturnsItFromTheRequestSuperglobal() {
        $data = time() * time();
        $_REQUEST['foo'] = $data;
        $this->assertEquals( $_REQUEST['foo'], $this->request->getValue('foo') );
        $this->assertEquals( $data, $this->request->getValue('foo') );
    }

    public function testDefaultIsUsedWhenGettingValueOfNonExistantParameter() {
        unset( $_REQUEST['foo'] );
        $this->assertEquals( 'bar', $this->request->getValue('foo','bar') );
    }

    public function testGetmethodReturnsTheMethodSpecifiedInTheServerSuperglobal() {
        $method = time() * time();
        $_SERVER['REQUEST_METHOD'] = $method;
        $this->assertEquals( $this->request->getMethod(), $method );
    }

}
