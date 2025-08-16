<?php

namespace Rollbar\WordPress\Tests\Html;

use Rollbar\WordPress\Html\Template;
use Rollbar\WordPress\Tests\BaseTestCase;

class TemplateTest extends BaseTestCase
{
    public function testString(): void
    {
        $result = Template::string(TEST_ROOT . '/fixtures/templates/test.php', [
            'foo' => 'test',
            'bar' => [
                'one',
                'two',
                'three',
            ],
        ]);

        self::assertStringContainsString('<h1>test</h1>', $result);
        self::assertStringContainsString('<li>one</li>', $result);
        self::assertStringContainsString('<li>two</li>', $result);
        self::assertStringContainsString('<li>three</li>', $result);
    }

    public function testPrint(): void
    {
        ob_start();
        Template::print(TEST_ROOT . '/fixtures/templates/test.php', [
            'foo' => 'test',
            'bar' => [
                'one',
                'two',
                'three',
            ],
        ]);
        $result = ob_get_clean();

        self::assertStringContainsString('<h1>test</h1>', $result);
        self::assertStringContainsString('<li>one</li>', $result);
        self::assertStringContainsString('<li>two</li>', $result);
        self::assertStringContainsString('<li>three</li>', $result);
    }
}
