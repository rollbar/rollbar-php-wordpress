<?php

namespace Rollbar\WordPress\Html\Input;

use Rollbar\WordPress\Html\Template;

// Exit if accessed directly
// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
defined('ABSPATH') || exit;
// phpcs:enable

/**
 * Boolean Input Class
 *
 * Renders a single checkbox input for a boolean setting.
 *
 * @since 3.0.0
 */
class BooleanInput extends AbstractInput
{
    /**
     * Renders the input as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return Template::string(ROLLBAR_PLUGIN_DIR . '/templates/html/input/booleanInput.php', ['input' => $this]);
    }

    /**
     * Returns the value of the input.
     *
     * @return bool
     */
    public function getValue(): bool
    {
        return boolval(parent::getValue());
    }

    /**
     * Returns the type of the input.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'boolean';
    }
}
