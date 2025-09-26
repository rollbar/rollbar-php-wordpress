<?php

namespace Rollbar\WordPress\Tests\Admin;

use Rollbar\WordPress\Admin\FlashMessages;
use Rollbar\WordPress\Tests\BaseTestCase;

class FlashMessagesTest extends BaseTestCase
{
    public function testAddMessage(): void
    {
        FlashMessages::addMessage('test message');

        $result = FlashMessages::flushMessages();

        self::assertStringContainsString('test message', $result);
    }

    public function testFlushMessages(): void
    {
        $result = FlashMessages::flushMessages();
        self::assertEmpty($result);

        FlashMessages::addMessage('test message');
        FlashMessages::addMessage('test message 2');

        $result = FlashMessages::flushMessages();

        self::assertStringContainsString('test message', $result);
        self::assertStringContainsString('test message 2', $result);
    }
}
