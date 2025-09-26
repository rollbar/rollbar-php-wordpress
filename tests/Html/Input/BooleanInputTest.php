<?php

namespace Rollbar\WordPress\Tests\Html\Input;

use Rollbar\WordPress\Html\Input\BooleanInput;
use Rollbar\WordPress\Tests\BaseTestCase;

class BooleanInputTest extends BaseTestCase
{
    private BooleanInput $subject;

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function set_up(): void
    {
        $this->subject = new BooleanInput(
            id: 'test',
            name: 'test_field',
            value: true,
            default: false,
            label: 'Test',
            helpText: 'Test help text',
            disabled: false,
            showReset: true,
            attributes: ['style' => 'width: 100px;'],
        );
    }

    public function testGetType(): void
    {
        self::assertSame('boolean', $this->subject->getType());
    }

    public function testGetId(): void
    {
        self::assertSame('test', $this->subject->getId());
    }

    public function testGetName(): void
    {
        self::assertSame('test_field', $this->subject->getName());
    }

    public function testGetValue(): void
    {
        self::assertTrue($this->subject->getValue());
    }

    public function testGetDefault(): void
    {
        self::assertFalse($this->subject->getDefault());
    }

    public function testGetLabel(): void
    {
        self::assertSame('Test', $this->subject->getLabel());
    }

    public function testGetHelpText(): void
    {
        self::assertSame('Test help text', $this->subject->getHelpText());
    }

    public function testIsDisabled(): void
    {
        self::assertFalse($this->subject->isDisabled());
    }

    public function testGetAttributes(): void
    {
        self::assertSame(['style' => 'width: 100px;'], $this->subject->getAttributes());
    }

    public function testSerializeAttributes(): void
    {
        self::assertSame('style="width: 100px;"', $this->subject->serializeAttributes());
    }

    public function testShowReset(): void
    {
        self::assertTrue($this->subject->showReset());
    }
}
