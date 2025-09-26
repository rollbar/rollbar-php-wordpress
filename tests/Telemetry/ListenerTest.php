<?php

namespace Rollbar\WordPress\Tests\Telemetry;

use Rollbar\Rollbar;
use Rollbar\Telemetry\EventLevel;
use Rollbar\Telemetry\EventType;
use Rollbar\WordPress\Telemetry\Listener;
use Rollbar\WordPress\Tests\BaseTestCase;
use Rollbar\WordPress\Tests\fixtures\TestEnum;

class ListenerTest extends BaseTestCase
{
    private Listener $subject;

    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function set_up(): void
    {
        Rollbar::init([
            'access_token' => $this->getAccessToken(),
        ]);
        $this->subject = Listener::getInstance();
    }

    public function testFullProcess(): void
    {
        $foo = '';
        $this->subject->instrumentAction(
            action: 'custom_listener_test_action',
            acceptedArgs: 2,
            argsHandler: function ($action, ...$args) use (&$foo) {
                $foo = $action;
                return 'custom_listener_test_action: ' . implode(', ', $args);
            },
        );
        self::assertSame('', $foo);

        do_action('custom_listener_test_action', 'foo', 'bar');
        self::assertSame('custom_listener_test_action', $foo);

        $events = Rollbar::getTelemeter()->copyEvents();
        $last = array_pop($events);

        self::assertSame('Action triggered: custom_listener_test_action: foo, bar', $last->body->message);
        self::assertSame(EventLevel::Info, $last->level);
        self::assertSame(EventType::Log, $last->type);
    }

    public function testInstrumentAction(): void
    {
        remove_all_actions('delete_user');

        $this->subject->instrumentAction(
            action: 'delete_user',
            acceptedArgs: 3,
            argsHandler: Listener::concatExtraArgs(...),
        );

        self::assertTrue(has_action('delete_user'));
    }

    /**
     * @dataProvider dataProviderConcatExtraArgs
     */
    public function testConcatExtraArgs(string $action, array $args, string $expected): void
    {
        self::assertSame($expected, Listener::concatExtraArgs($action, ...$args));
    }

    public function testConcatExtraArgsObject(): void
    {
        self::assertSame(
            'widgets_init: object(stdClass)',
            Listener::concatExtraArgs('widgets_init', new \stdClass()),
        );
        self::assertSame(
            'widgets_init: object(Rollbar\WordPress\Telemetry\Listener)',
            Listener::concatExtraArgs('widgets_init', $this->subject),
        );
    }

    public function testLog(): void
    {
        $this->subject->log('test', EventLevel::Critical);

        $events = Rollbar::getTelemeter()->copyEvents();
        $last = array_pop($events);

        self::assertSame('test', $last->body->message);
        self::assertSame(EventLevel::Critical, $last->level);
    }

    public static function dataProviderConcatExtraArgs(): array
    {
        $file = fopen('php://memory', 'r+');
        return [
            ['delete_user', [true], 'delete_user: true'],
            ['register_post', [true, 1], 'register_post: true, 1'],
            ['wpmu_new_user', ['foo', 'bar'], 'wpmu_new_user: foo, bar'],
            ['check_admin_referer', [false], 'check_admin_referer: false'],
            ['admin_init', [[1, 2, 3, 'four']], 'admin_init: Array(4)'],
            ['wp_footer', [null], 'wp_footer: null'],
            ['edit_link', [5.84], 'edit_link: 5.84'],
            ['edit_link', [(object)['id' => 123]], 'edit_link: object(stdClass) {id: 123}'],
            ['edit_link', [TestEnum::Bar], 'edit_link: enum(Rollbar\WordPress\Tests\fixtures\TestEnum::Bar)'],
            ['edit_link', [$file], 'edit_link: resource(stream: ' . get_resource_id($file) . ')'],
            ['edit_link', [self::dataProviderConcatExtraArgs(...)], 'edit_link: closure'],
        ];
    }
}
