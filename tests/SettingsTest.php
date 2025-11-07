<?php

namespace Rollbar\WordPress\Tests;

use Rollbar\WordPress\Lib\AbstractSingleton;
use Rollbar\WordPress\Settings;
use Rollbar\WordPress\Settings\SettingType;

/**
 * Class SettingsTest
 *
 * @package Rollbar\WordPress\Tests
 */
class SettingsTest extends BaseTestCase
{
    public function testSettings(): void
    {
        $settings = Settings::settings();

        // Ensure a sample of the settings are present
        self::assertArrayHasKey('php_logging_enabled', $settings);
        self::assertArrayHasKey('js_logging_enabled', $settings);
        self::assertArrayHasKey('environment', $settings);
        self::assertArrayHasKey('agent_log_location', $settings);
        self::assertArrayHasKey('endpoint', $settings);
        self::assertArrayHasKey('telemetry_hooks', $settings);

        // Ensure some important settings are not included in the list.
        self::assertNull($settings['access_token']);
        self::assertNull($settings['check_ignore']);
        self::assertNull($settings['enabled']);
        self::assertNull($settings['person']);
        self::assertNull($settings['person_fn']);
        self::assertNull($settings['telemetry']);
    }

    public function testGetSettingType(): void
    {
        self::assertNull(Settings::getSettingType('access_token'));
        self::assertNull(Settings::getSettingType('enabled'));

        self::assertSame(SettingType::Boolean, Settings::getSettingType('php_logging_enabled'));
        self::assertSame(SettingType::Boolean, Settings::getSettingType('js_logging_enabled'));
        self::assertSame(SettingType::Text, Settings::getSettingType('environment'));
        self::assertSame(SettingType::Select, Settings::getSettingType('included_errno'));
        self::assertSame(SettingType::Integer, Settings::getSettingType('max_items'));
        self::assertSame(SettingType::Text, Settings::getSettingType('endpoint'));
        self::assertSame(SettingType::CheckBox, Settings::getSettingType('telemetry_hooks'));
    }

    public function testGetDefaultSetting(): void
    {
        $settings = Settings::getInstance();
        self::assertEquals('production', $settings->getDefaultOption('environment'));
        self::assertEquals('https://api.rollbar.com/api/1/', $settings->getDefaultOption('endpoint'));
        self::assertTrue($settings->getDefaultOption('capture_error_stacktraces'));
    }

    /**
     * @dataProvider preUpdateProvider
     */
    public function testPreUpdate($expected, $data): void
    {
        self::assertEquals(
            $expected,
            Settings::preUpdate($data),
        );
    }

    public function testSet(): void
    {
        $settings = Settings::getInstance();
        $settings->set('php_logging_enabled', true);
        self::assertTrue($settings->get('php_logging_enabled'));
        $settings->set('foo', 'bar');
        self::assertSame('bar', $settings->get('foo'));
    }

    /**
     * @dataProvider toBooleanProvider
     */
    public function testToBoolean(mixed $value, bool $expected): void
    {
        self::assertSame($expected, Settings::toBoolean($value));
    }

    /**
     * @dataProvider toIntegerProvider
     */
    public function testToInteger(mixed $value, int $expected): void
    {
        self::assertSame($expected, Settings::toInteger($value));
    }

    /**
     * @dataProvider toStringProvider
     */
    public function testToString(mixed $value, string $expected): void
    {
        self::assertSame($expected, Settings::toString($value));
    }

    /**
     * @dataProvider toArrayStringProvider
     */
    public function testToStringArray(mixed $value, array $expected): void
    {
        self::assertSame($expected, Settings::toStringArray($value));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRollbarSettingsConstant(): void
    {
        define('ROLLBAR_SETTINGS', [
            'scrub_fields' => ['bar'],
        ]);

        // Remove the singleton instance to ensure the constant is read.
        $ref = new \ReflectionClass(AbstractSingleton::class);
        $ref->setStaticPropertyValue('instances', []);

        self::assertSame(['bar'], Settings::getInstance()->get('scrub_fields'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRollbarSettingsConstantPriority(): void
    {
        define('ROLLBAR_SETTINGS', [
            'server_side_access_token' => 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb',
        ]);
        putenv('ROLLBAR_ACCESS_TOKEN=aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa');

        // Remove the singleton instance to ensure the constant is read.
        $ref = new \ReflectionClass(AbstractSingleton::class);
        $ref->setStaticPropertyValue('instances', []);

        self::assertSame('bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb', Settings::getInstance()->get('server_side_access_token'));
    }

    public function testNormalizeSettings(): void
    {
        $actual = Settings::normalizeSettings([
            'php_logging_enabled' => 'true',
            'js_logging_enabled' => 'false',
            'environment' => 'production',
            'included_errno' => '256',
            'max_items' => '100',
            'endpoint' => 'https://api.rollbar.com/api/1/',
            'telemetry_hooks' => [
                'init',
            ],
        ]);

        self::assertArrayIsIdenticalToArrayOnlyConsideringListOfKeys(
            [
                'php_logging_enabled' => true,
                'js_logging_enabled' => false,
                'server_side_access_token' => '',
                'client_side_access_token' => '',
                'included_errno' => '256',
                'environment' => 'production',
                'endpoint' => 'https://api.rollbar.com/api/1/',
                'max_items' => 100,
                'telemetry_hooks' => [
                    'init',
                ],
            ],
            $actual,
            [
                'php_logging_enabled',
                'js_logging_enabled',
                'server_side_access_token',
                'client_side_access_token',
                'included_errno',
                'environment',
                'max_items',
                'endpoint',
                'telemetry_hooks',
            ],
        );
    }

    /**
     * @return array{
     *     expected: array<string, bool>,
     *     data: array<string, bool>,
     * }[]
     */
    public static function preUpdateProvider(): array
    {
        return [
            [
                'expected' => [
                    'php_logging_enabled' => false,
                    'js_logging_enabled' => false,
                ],
                'data' => [
                    'allow_exec' => true,
                    'capture_error_stacktraces' => true,
                    'local_vars_dump' => true,
                    'capture_ip' => true,
                    'transmit' => true,
                    'enable_telemetry_listener' => true,
                    'include_items_in_telemetry' => true,
                ],
            ],
            [
                'expected' => [
                    'php_logging_enabled' => true,
                    'js_logging_enabled' => false,
                    'enabled' => true,
                ],
                'data' => [
                    'php_logging_enabled' => true,
                    'allow_exec' => true,
                    'capture_error_stacktraces' => true,
                    'local_vars_dump' => true,
                    'capture_ip' => true,
                    'transmit' => true,
                    'enable_telemetry_listener' => true,
                    'include_items_in_telemetry' => true,
                ],
            ],
            [
                'expected' => [
                    'allow_exec' => false,
                    'php_logging_enabled' => false,
                    'js_logging_enabled' => false,
                ],
                'data' => [
                    'allow_exec' => false,
                    'capture_error_stacktraces' => true,
                    'local_vars_dump' => true,
                    'capture_ip' => true,
                    'transmit' => true,
                    'enable_telemetry_listener' => true,
                    'include_items_in_telemetry' => true,
                ],
            ],
            [
                'expected' => [
                    'use_error_reporting' => true,
                    'php_logging_enabled' => false,
                    'js_logging_enabled' => false,
                ],
                'data' => [
                    'use_error_reporting' => true,
                    'allow_exec' => true,
                    'capture_error_stacktraces' => true,
                    'local_vars_dump' => true,
                    'capture_ip' => true,
                    'transmit' => true,
                    'enable_telemetry_listener' => true,
                    'include_items_in_telemetry' => true,
                ],
            ],
        ];
    }

    public static function toBooleanProvider(): array
    {
        return [
            [true, true],
            [false, false],
            ['false', false],
            ['true', true],
            ['1', true],
            ['0', false],
            ['', false],
            ['yes', true],
            ['no', false],
            ['on', true],
            ['off', false],
            [0, false],
            [1, true],
            [-1, true],
        ];
    }

    public static function toIntegerProvider(): array
    {
        return [
            [true, 1],
            [false, 0],
            [42, 42],
            [42.9, 42],
            ["42.9", 42],
            [null, 0],
            [['foo'], 0],
        ];
    }

    public static function toStringProvider(): array
    {
        return [
            [true, 'true'],
            [false, 'false'],
            ['foo', 'foo'],
            [42.9, '42.9'],
            [null, ''],
            [['bar'], ''],
            [42, '42'],
        ];
    }

    public static function toArrayStringProvider(): array
    {
        return [
            [[], []],
            ['foo', ['foo']],
            [['foo'], ['foo']],
            [['foo', 'bar'], ['foo', 'bar']],
            [[true, 42], ['true', '42']],
        ];
    }
}
