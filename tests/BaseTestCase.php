<?php

namespace Rollbar\WordPress\Tests;

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Class BaseTestCase
 *
 * @package Rollbar\WordPress\Tests
 */
abstract class BaseTestCase extends TestCase
{
    public function getAccessToken(): string
    {
        return $_ENV['ROLLBAR_TEST_TOKEN'] ?? '';
    }

    public function getEnvironment(): string
    {
        return 'testing';
    }
}
