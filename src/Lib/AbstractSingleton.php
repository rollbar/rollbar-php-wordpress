<?php

namespace Rollbar\WordPress\Lib;

use Exception;

// Exit if accessed directly
// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
defined('ABSPATH') || exit;
// phpcs:enable

/**
 * This is an abstract singleton class that provides a way to create a singleton instance of a class.
 *
 * @since 3.0.0
 */
abstract class AbstractSingleton
{
    /**
     * The collection of singleton instances.
     *
     * The array key is the class name, and the value is the instance of that class.
     *
     * @var array<string, static> $instances
     */
    private static array $instances = [];

    /**
     * Returns the singleton instance of the class.
     *
     * @return static
     */
    public static function getInstance(): static
    {
        if (!array_key_exists(static::class, self::$instances)) {
            self::$instances[static::class] = new static();
            self::$instances[static::class]->postInit();
        }

        return self::$instances[static::class];
    }

    /**
     * Method called after the class is constructed.
     *
     * This can be useful for setting up other singleton classes or performing actions that don't fit in the
     * constructor.
     *
     * @return void
     */
    protected function postInit(): void
    {
    }

    /**
     * Singletons should not be cloneable.
     *
     * @return void
     */
    public function __clone(): void
    {
    }

    /**
     * Singletons should not be restored from strings.
     *
     * @return void
     * @throws Exception If attempting to unserialize a singleton.
     */
    public function __wakeup(): void
    {
        throw new Exception('Cannot unserialize a singleton.');
    }
}
