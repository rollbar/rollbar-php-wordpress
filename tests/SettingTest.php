<?php

namespace Rollbar\WordPress\Tests;

use Rollbar\WordPress\Setting;
use Rollbar\WordPress\Settings\SettingType;

class SettingTest extends BaseTestCase
{
    public function testConstruct()
    {
        $setting = new Setting(
            id: 'foo',
            type: SettingType::Integer,
            label: 'Foo',
            helpText: 'Foo help text',
            default: 1,
        );

        self::assertSame('foo', $setting->id);
        self::assertSame(SettingType::Integer, $setting->type);
        self::assertSame('Foo', $setting->label);
        self::assertSame('Foo help text', $setting->helpText);
        self::assertSame(1, $setting->default);
        self::assertSame(false, $setting->alwaysSave);
    }

    public function testGetTitle(): void
    {
        $setting = new Setting(
            id: 'foo',
            type: SettingType::Integer,
            label: 'Foo',
            helpText: 'Foo help text',
            default: 1,
        );
        self::assertSame('Foo', $setting->getTitle());

        $setting = new Setting(
            id: 'foo_bar',
            type: SettingType::Integer,
        );

        self::assertSame('Foo Bar', $setting->getTitle());
    }

    public function testRender(): void
    {
        $setting = new Setting(
            id: 'php_logging_enabled',
            type: SettingType::Boolean,
        );

        ob_start();
        $setting->render(['value' => true]);
        $output = ob_get_clean();

        self::assertStringContainsString('id="rollbar_wp_php_logging_enabled"', $output);
        self::assertStringContainsString('checked', $output);
        self::assertStringContainsString('type="checkbox"', $output);
    }
}
