<?php
namespace Rollbar\Wordpress\Tests;

use Yoast\PHPUnitPolyfills\TestCases\TestCase;


/**
 * Class BaseTestCase
 *
 * @package Rollbar\Wordpress\Tests
 */
abstract class BaseTestCase extends TestCase {
    
    function getAccessToken()
    {
        return $_ENV['ROLLBAR_TEST_TOKEN'];
    }
    
    function getEnvironment()
    {
        return "testing";
    }
    
}