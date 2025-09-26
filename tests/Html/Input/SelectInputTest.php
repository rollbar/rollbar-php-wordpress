<?php

namespace Rollbar\WordPress\Tests\Html\Input;

use Rollbar\WordPress\Html\Input\SelectInput;
use Rollbar\WordPress\Tests\BaseTestCase;

class SelectInputTest extends BaseTestCase
{
    private SelectInput $subject;

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function set_up(): void
    {
        $this->subject = new SelectInput(
            id: 'test',
            name: 'test',
            value: 'value_1',
            default: false,
            label: 'Test',
            helpText: 'Test help text',
            disabled: false,
            showReset: true,
            attributes: ['style' => 'width: 100px;'],
            options: [
                'value_2' => 'Value 2',
                'value_1' => 'Value 1',
            ],
        );
    }

    public function testGetType(): void
    {
        self::assertSame('select', $this->subject->getType());
    }

    public function testGetValue(): void
    {
        self::assertSame('value_1', $this->subject->getValue());
    }
}
