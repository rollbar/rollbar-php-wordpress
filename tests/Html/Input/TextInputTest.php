<?php

namespace Rollbar\WordPress\Tests\Html\Input;

use Rollbar\WordPress\Html\Input\TextInput;
use Rollbar\WordPress\Tests\BaseTestCase;

class TextInputTest extends BaseTestCase
{
    private TextInput $subject;

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function set_up(): void
    {
        $this->subject = new TextInput(
            id: 'test',
            name: 'test',
            value: 'test_value',
        );
    }

    public function testType(): void
    {
        self::assertSame('text', $this->subject->getType());
    }

    public function testValue(): void
    {
        self::assertSame('test_value', $this->subject->getValue());
    }
}
