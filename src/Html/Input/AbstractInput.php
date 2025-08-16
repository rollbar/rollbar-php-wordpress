<?php

namespace Rollbar\WordPress\Html\Input;

// Exit if accessed directly
// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
defined('ABSPATH') || exit;
// phpcs:enable

/**
 * The abstract class for all input types.
 *
 * @since 3.0.0
 */
abstract class AbstractInput implements InputInterface
{
    /**
     * Constructor for the base input.
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
    ) {
    }

    /**
     * Returns the input ID.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Returns the value of the HTML name attribute.
     *
     * @return string
     */
    public function getName(): string
    {
        if (!is_array($this->name)) {
            return $this->name;
        }
        $name = $this->name[0];
        if (1 < count($this->name)) {
            $name .= '[' . implode('][', array_slice($this->name, 1)) . ']';
        }
        return $name;
    }

    /**
     * Returns the value of the input.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the value of the input.
     *
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * Returns the default value of the input.
     *
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Returns the label of the input.
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Returns the help text to be displayed with the input.
     *
     * @return string
     */
    public function getHelpText(): string
    {
        return $this->helpText;
    }

    /**
     * Returns true if the input is disabled, false otherwise.
     *
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * Returns a list of HTML attributes to be applied to the input.
     *
     * @return array<string, string>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Returns the serialized HTML attributes of the input.
     *
     * @return string
     */
    public function serializeAttributes(): string
    {
        if (empty($this->attributes)) {
            return '';
        }
        $result = '';
        foreach ($this->attributes as $key => $value) {
            if (is_bool($value) || is_null($value)) {
                $result .= ' ' . $key;
                continue;
            }
            $result .= ' ' . $key . '="' . esc_attr($value) . '"';
        }
        return trim($result);
    }

    /**
     * Returns true if the reset button should be displayed, false otherwise.
     *
     * @return bool
     */
    public function showReset(): bool
    {
        return $this->showReset;
    }
}
