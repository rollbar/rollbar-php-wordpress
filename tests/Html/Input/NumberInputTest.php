<?php

namespace Rollbar\WordPress\Tests\Html\Input;

use Rollbar\WordPress\Html\Input\NumberInput;
use Rollbar\WordPress\Tests\BaseTestCase;

class NumberInputTest extends BaseTestCase
{
    private NumberInput $subject;

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function set_up(): void
    {
        $this->subject = new NumberInput(
            id: 'test',
            name: 'test',
            value: 42,
        );
    }

    public function testType(): void
    {
        self::assertSame('number', $this->subject->getType());
    }

    public function testValue(): void
    {
        self::assertSame(42, $this->subject->getValue());
    }
}
