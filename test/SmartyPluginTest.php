<?php
require_once 'PHPUnit/Framework/TestCase.php';
require_once dirname(__FILE__) . '/TestEnv.php';

class SmartyPluginTest extends PHPUnit_Framework_TestCase {
    protected function exceptionMessageTest($code, $expect) {
        try {
            eval($code);
        } catch (Exception $e) {
            $this->assertEqualsIgnoreSeparator($expect, $e->getMessage());
        }
    }


    protected function assertFileEqualsIgnoreSeparator($expect, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = FALSE, $ignoreCase = FALSE) {
        $this->assertEquals(
            str_replace("\r\n", "\n", file_get_contents($expect)),
            str_replace("\r\n", "\n", file_get_contents($actual)),
            $message,
            $delta,
            $maxDepth,
            $canonicalize,
            $ignoreCase
        );
    }

    protected function assertEqualsIgnoreSeparator($expect, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = FALSE, $ignoreCase = FALSE) {
        $this->assertEquals(
            str_replace("\r\n", "\n", $expect),
            str_replace("\r\n", "\n", $actual),
            $message,
            $delta,
            $maxDepth,
            $canonicalize,
            $ignoreCase
        );
    }

    protected function assertContainsIgnoreSeparator($needle, $haystack, $message = '', $ignoreCase = false, $checkForObjectIdentity = TRUE) {
        $this->assertContains(
            str_replace("\r\n", "\n", $needle),
            str_replace("\r\n", "\n", $haystack),
            $message,
            $ignoreCase,
            $checkForObjectIdentity
        );
    }

    protected function assertArrayEqualsIgnoreOrder($expected, $actual, $msg = ''){
        $this->assertEquals(count($expected), count($actual), $msg);
        foreach($actual as $value){
            $this->assertTrue(in_array($value, $expected), $msg);
        }
    }
}