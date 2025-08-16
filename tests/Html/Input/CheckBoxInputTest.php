<?php

namespace Rollbar\WordPress\Tests\Html\Input;

use Rollbar\WordPress\Html\Input\CheckBoxInput;
use Rollbar\WordPress\Tests\BaseTestCase;

class CheckBoxInputTest extends BaseTestCase
{
    private CheckBoxInput $subject;

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function set_up(): void
    {
        $this->subject = new CheckBoxInput(
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
            sort: true,
        );
    }

    public function testSort()
    {
        self::assertSame([
            'value_1' => 'Value 1',
            'value_2' => 'Value 2',
        ], $this->subject->getOptions());
    }

    public function testGetValue(): void
    {
        self::assertSame(['value_1'], $this->subject->getValue());

        $this->subject->setValue(['value_2']);
        self::assertSame(['value_2'], $this->subject->getValue());

        $this->subject->setValue(['value_1', 'value_2']);
        self::assertSame(['value_1', 'value_2'], $this->subject->getValue());
    }

    public function testGetType(): void
    {
        self::assertSame('checkbox', $this->subject->getType());
    }
}
