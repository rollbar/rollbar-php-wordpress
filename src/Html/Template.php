<?php

namespace Rollbar\WordPress\Html;

// Exit if accessed directly
// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
defined('ABSPATH') || exit;
// phpcs:enable

/**
 * Class Template
 *
 * This class is responsible for rendering PHP templates.
 *
 * @since 3.0.0
 */
class Template
{
    /**
     * The last data array used during a render.
     *
     * @var array $data
     */
    private static array $data = [];

    /**
     * The last path used during a render.
     *
     * @var string $path
     */
    private static string $path = '';

    /**
     * Renders a PHP template to a string, and returns it.
     *
     * @param string $path The absolute path to the template PHP file.
     * @param array $data The data that will be passed to the template. Each key will become a local variable in the
     * template.
     *
     * @return string
     */
    public static function string(string $path = '', array $data = []): string
    {
        ob_start();
        self::print($path, $data);
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

    /**
     * Renders and echos a PHP template.
     *
     * @param string $path The absolute path to the template PHP file.
     * @param array $data The data that will be passed to the template. Each key will become a local variable in the
     * template.
     *
     * @return void
     */
    public static function print(string $path = '', array $data = []): void
    {
        // Temporarily store local variables in the object, so they are not overridden during extract.
        self::$path = $path;
        self::$data = $data;

        // Remove local variables from scope.
        unset($path);
        unset($data);

        extract(self::$data);
        include self::$path;
    }
}
