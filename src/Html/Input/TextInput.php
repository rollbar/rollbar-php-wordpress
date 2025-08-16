<?php

namespace Rollbar\WordPress\Html\Input;

use Rollbar\WordPress\Html\Template;

// Exit if accessed directly
// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
defined('ABSPATH') || exit;
// phpcs:enable

/**
 * Text Input Class
 *
 * Renders a text input field.
 *
 * @since 3.0.0
 */
class TextInput extends AbstractInput
{
    /**
     * Renders the input as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return Template::string(ROLLBAR_PLUGIN_DIR . '/templates/html/input/textInput.php', ['input' => $this]);
    }

    /**
     * Returns the type of the input.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'text';
    }
}
