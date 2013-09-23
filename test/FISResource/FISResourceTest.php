<?php
require_once(dirname(__FILE__) . '/../SmartyPluginTest.php');
require_once(PLUGIN_DIR . 'FISResource.class.php');

class Smarty {
    public function getConfigDir() {
        return array(
            dirname(__FILE__) . '/projects/config/'
        );
    }
}
class FISResourceTest extends SmartyPluginTest {

    public function setUp() {
        FISResource::reset();
    }

    public function tearDown() {
    }

    //test not find ID from map file;
    public function testLoadEmpty() {
        $this->exceptionMessageTest(
            "FISResource::load('empty:static/common/ui/a/a.js', new Smarty());",
            'undefined resource "empty:static/common/ui/a/a.js"'
        );
    }

    public function testLoadAsync() {
        FISResource::load('common:static/common/index/index.js', new Smarty());
        $this->assertEqualsIgnoreSeparator(
            FISResource::getResourceMap(),
            '{"res":{"common:static/common/ui/async/async.js":{"url":"/static/common/ui/async/async.js","deps":[]}}}'
        );
    }

    public function testLoadCycle() {
    }
}