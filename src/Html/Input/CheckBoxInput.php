<?php

namespace Rollbar\WordPress\Html\Input;

use Rollbar\WordPress\Html\Template;

// Exit if accessed directly
// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
defined('ABSPATH') || exit;
// phpcs:enable

/**
 * CheckBox Input Class
 *
 * Renders a set of checkbox inputs with labels, allowing multiple values to be selected.
 *
 * @since 3.0.0
 */
class CheckBoxInput extends AbstractInput
{
    /**
     * Constructor for the CheckBoxInput class.
     *
     * @param string $id The input ID.
     * @param string|array $name The input name.
     * @param mixed $value The input value.
     * @param mixed $default The default value.
     * @param string $label The label for the input.
     * @param string $helpText The help text for the input.
     * @param bool $disabled Whether the input is disabled.
     * @param bool $showReset Whether to show the reset button.
     * @param array<string, string> $attributes Additional attributes for the input.
     * @param array<string, string> $options The value, label pairs of possible options.
     * @param bool $sort True to sort the options by key.
     */
    public function __construct(
        protected string $id,
        protected string|array $name,
        protected $value = null,
        protected $default = null,
        protected string $label = '',
        protected string $helpText = '',
        protected bool $disabled = false,
        protected bool $showReset = true,
        protected array $attributes = [],
        protected array $options = [],
        protected bool $sort = false,
    ) {
        parent::__construct($id, $name, $value, $default, $label, $helpText, $disabled, $showReset, $attributes);
        if ($this->sort) {
            ksort($this->options);
        }
    }

    /**
     * Renders the input as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return Template::string(ROLLBAR_PLUGIN_DIR . '/templates/html/input/checkBoxInput.php', ['input' => $this]);
    }

    /**
     * Returns the value, label pairs of possible options.
     *
     * @return array<string, string>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Returns the selected value(s) of the input.
     *
     * @return string[]
     */
    public function getValue(): array
    {
        if (is_array($this->value)) {
            return $this->value;
        }
        return $this->value ? [$this->value] : [];
    }

    /**
     * Returns the type of the input.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'checkbox';
    }
}
