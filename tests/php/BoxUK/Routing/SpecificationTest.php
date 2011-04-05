<?php

namespace BoxUK\Routing;

require_once 'tests/php/bootstrap.php';

class SpecificationTest extends \PHPUnit_Framework_TestCase {

    public function testConstructor() {
        $spec = new Specification( 'route', 'controller', 'action', array(1), 'POST' );
        $this->assertEquals( 'route', $spec->getRoute() );
        $this->assertEquals( 'controller', $spec->getController() );
        $this->assertEquals( 'action', $spec->getAction() );
        $this->assertEquals( array(1), $spec->getParameters() );
        $this->assertEquals( 'POST', $spec->getMethod() );
    }

    public function testDefaultMethodIsBlank() {
        $spec = new Specification( 'route', 'controller', 'action', array(1) );
        $this->assertEquals( '', $spec->getMethod() );
    }

}
