<?php

namespace Rollbar\WordPress\Tests;

use Rollbar\Payload\Level;
use Rollbar\Rollbar;
use Rollbar\WordPress\Plugin;

/**
 * Class PluginTest
 *
 * @package Rollbar\WordPress\Tests
 */
class PluginTest extends BaseTestCase
{
    private Plugin $subject;

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function set_up(): void
    {
        $this->subject = Plugin::getInstance();
    }

    public function testConfigure(): void
    {
        $expected = 'testConfigure';

        $plugin = $this->subject;

        $plugin->setSetting('php_logging_enabled', 1);
        $plugin->setSetting(
            'included_errno',
            Plugin::buildIncludedErrno(E_WARNING),
        );
        $plugin->setSetting('server_side_access_token', $this->getAccessToken());
        $plugin->setSetting('environment', $expected);
        $plugin->configure(['environment' => $expected]);

        $plugin->initPhpLogging();

        $dataBuilder = Rollbar::logger()->getDataBuilder();
        $output = $dataBuilder->makeData(Level::ERROR, 'testing', []);

        self::assertEquals($expected, $output->getEnvironment());

        $expected = 'testConfigure2';

        $plugin->configure(['environment' => $expected]);
        $dataBuilder = Rollbar::logger()->getDataBuilder();

        $output = $dataBuilder->makeData(Level::ERROR, 'testing', []);

        self::assertEquals($expected, $output->getEnvironment());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDisableAdmin(): void
    {
        self::assertFalse(Plugin::disabledAdmin());
        define('ROLLBAR_DISABLE_ADMIN', true);
        self::assertTrue(Plugin::disabledAdmin());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDisableAdmin2(): void
    {
        self::assertFalse(Plugin::disabledAdmin());
        define('ROLLBAR_DISABLE_ADMIN', false);
        self::assertFalse(Plugin::disabledAdmin());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testHideAdmin(): void
    {
        add_action('rollbar_disable_admin', '__return_false');
        self::assertFalse(Plugin::hideAdmin());
        add_action('rollbar_disable_admin', '__return_true');
        self::assertTrue(Plugin::hideAdmin());
    }

    public function testGetAssetUrl(): void
    {
        self::assertStringEndsWith('/wp-content/plugins/rollbar/test.js', Plugin::getAssetUrl('test.js'));
    }

    /**
     * @dataProvider loggingLevelTestDataProvider
     */
    public function testIncludedErrno(
        $loggingLevel,
        $errorLevel,
        $errorMsg,
        $shouldIgnore,
    ): void {
        $plugin = $this->subject;

        $plugin->setSetting('php_logging_enabled', 1);
        $plugin->setSetting(
            'included_errno',
            Plugin::buildIncludedErrno($loggingLevel),
        );
        $plugin->setSetting('server_side_access_token', $this->getAccessToken());
        $plugin->setSetting('environment', $this->getEnvironment());

        $plugin->initPhpLogging();

        $logger = Rollbar::logger();
        $dataBuilder = $logger->getDataBuilder();

        $errorWrapper = $dataBuilder->generateErrorWrapper(
            $errorLevel,
            $errorMsg,
            '',
            0,
        );

        $response = $logger->report(Level::ERROR, $errorWrapper);

        if ($shouldIgnore) {
            self::assertEquals('Ignored', $response->getInfo());
        } else {
            self::assertNotEquals('Ignored', $response->getInfo());
        }
    }

    /**
     * Tests to ensure bools are properly converted from "1" and "0" strings
     *
     * @ticket https://github.com/rollbar/rollbar-php-wordpress/issues/119
     */
    public function testSendMessageTraceBool(): void
    {
        $plugin = $this->subject;

        $plugin->setSetting('send_message_trace', '1');
        $plugin->setSetting('report_suppressed', '0');
        $data = $plugin->buildPHPConfig();

        self::assertTrue($data['send_message_trace']);
        self::assertFalse($data['report_suppressed']);
    }

    public static function loggingLevelTestDataProvider(): array
    {
        return [
            [
                E_ERROR, // Plugin logging level
                E_WARNING, // Triggered error code
                'This error should get ignored.',
                true, // Expected 'Ignored' ?
            ],
            [ // Should get reported to Rollbar
                E_WARNING, // Plugin logging level
                E_WARNING, // Triggered error code
                'This E_WARNING triggered with logging level E_WARNING should get reported.',
                false, // Expected 'Ignored' ?
            ],
            [ // Should get reported to Rollbar
                E_WARNING, // Plugin logging level
                E_ERROR, // Triggered error code
                'This E_ERROR triggered with logging level E_WARNING should get reported.',
                false, // Expected 'Ignored' ?
            ],
            [ // Should get reported to Rollbar
                E_ALL, // Plugin logging level
                E_ERROR, // Triggered error code
                'This E_ERROR triggered with logging level E_ALL should get reported.',
                false, // Expected 'Ignored' ?
            ],
        ];
    }

    public function testBuildIncludedErrno(): void
    {
        $expected = (E_ERROR | E_WARNING);

        $result = Plugin::buildIncludedErrno(E_WARNING);

        self::assertEquals((E_ERROR | E_WARNING), $result);

        $result = Plugin::buildIncludedErrno(E_NOTICE);

        self::assertNotEquals((E_ERROR | E_WARNING), $result);

        $result = Plugin::buildIncludedErrno(E_NOTICE);

        self::assertEquals((E_ERROR | E_WARNING | E_PARSE | E_NOTICE), $result);
    }
}
