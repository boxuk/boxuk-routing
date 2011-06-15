<?php

namespace BoxUK\Routing;

require_once 'tests/php/bootstrap.php';

class ConfigurationTest extends \PHPUnit_Framework_TestCase {
    
    private $config, $invalidStrings;
    
    public function setUp() {
        $this->config = new Configuration;
        $this->invalidStrings = array(1, null, false, true);
    }
    
    public function testThrowsExceptionWhenRoutesFileIsNotAString() {        
        foreach ($this->invalidStrings as $string) {
            try {
                $this->config->setRoutesFile($string);
                $this->fail('Cannot set routes file as an invalid string');
            }
            catch( \InvalidArgumentException $e) {
                // pass
            }
        }   
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsExceptionWhenRoutesFileIsNotAFile() {
        $this->config->setRoutesFile('/some/invalid/path');
    }
    
    public function testCanSetRoutesFile() {
        $routesFile = getcwd() . '/tests/resources/routes.spec';
        $this->config->setRoutesFile($routesFile);
        
        $this->assertEquals($routesFile, $this->config->getRoutesFile());
    }
    
    public function testThrowsExceptionWhenExtensionIsNotAString() {        
        foreach ($this->invalidStrings as $string) {
            try {
                $this->config->setExtension($string);
                $this->fail('Cannot set extension as an invalid string');
            }
            catch( \InvalidArgumentException $e) {
                // pass
            }
        }   
    }
    
    public function testCanSetExtension() {
        $extension = '.html';
        $this->config->setExtension($extension);
        
        $this->assertEquals($extension, $this->config->getExtension());
    }
    
    public function testThrowsExceptionWhenSiteDomainIsNotAString() {        
        foreach ($this->invalidStrings as $string) {
            try {
                $this->config->setSiteDomain($string);
                $this->fail('Cannot set site domain as an invalid string');
            }
            catch( \InvalidArgumentException $e) {
                // pass
            }
        }   
    }
    
    public function testCanSetSiteDomain() {
        $siteDomain = 'mysite.com';
        $this->config->setSiteDomain($siteDomain);
        
        $this->assertEquals($siteDomain, $this->config->getSiteDomain());
    }
    
    public function testThrowsExceptionWhenSiteWebRootIsNotAString() {        
        foreach ($this->invalidStrings as $string) {
            try {
                $this->config->setSiteWebRoot($string);
                $this->fail('Cannot set site web root as an invalid string');
            }
            catch( \InvalidArgumentException $e) {
                // pass
            }
        }   
    }
    
    public function testCanSetSiteWebRoot() {
        $webRoot = '/path';
        $this->config->setSiteWebroot($webRoot);
        
        $this->assertEquals($webRoot, $this->config->getSiteWebroot());
    }
    
}