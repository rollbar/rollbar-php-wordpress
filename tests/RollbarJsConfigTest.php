<?php

namespace Rollbar\WordPress\Tests;

use Rollbar\WordPress\Plugin;

/**
 * Class RollbarJsConfigTest
 *
 * @package Rollbar\WordPress\Tests
 */
class RollbarJsConfigTest extends BaseTestCase
{
    public function testRollbarJsConfig(): void
    {
        $expected = [
            'id' => '1',
            'username' => 'test',
            'email' => 'wptest@rollbar.com',
        ];

        \add_filter('rollbar_js_config', function ($config) use ($expected) {
            $config['payload']['person'] = $expected;

            return $config;
        });

        $plugin = Plugin::getInstance();
        $plugin->setSetting('client_side_access_token', 'XXX');
        $plugin->setSetting('js_logging_enabled', '1');

        $jsConfig = $plugin->buildJsConfig();

        self::assertEquals($expected, $jsConfig['payload']['person']);
    }
}
