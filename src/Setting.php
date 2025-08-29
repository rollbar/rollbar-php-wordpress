<?php

namespace Rollbar\WordPress;

use Rollbar\WordPress\Html\Input\InputInterface;
use Rollbar\WordPress\Settings\SettingType;

// Exit if accessed directly
// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
defined('ABSPATH') || exit;
// phpcs:enable

/**
 * A class representing a single setting for the Rollbar WP Plugin.
 *
 * @since 3.0.0
 */
final class Setting
{
    /**
     * The input object for the setting.
     *
     * @var InputInterface|null
     */
    private readonly null|InputInterface $input;

    /**
     * Constructor for the Setting class.
     *
     * @param string $id The setting ID.
     * @param SettingType $type The type of the setting.
     * @param string $label The label for the setting.
     * @param string $helpText The help text for the setting.
     * @param mixed|null $default The default value for the setting.
     * @param array $options The options for the setting.
     * @param string $section The section of the admin page where the setting will be displayed.
     * @param array $inputArgs Additional arguments for the input element.
     */
    public function __construct(
        readonly public string $id,
        readonly public SettingType $type,
        readonly public string $label = '',
        readonly public string $helpText = '',
        readonly public mixed $default = null,
        readonly public array $options = [],
        readonly public string $section = 'rollbar_wp_advanced',
        readonly public array $inputArgs = [],
    ) {
        $this->input = $this->createInput();
    }

    /**
     * Returns the formatted label.
     *
     * @return string
     */
    public function getTitle(): string
    {
        if (!empty($this->label)) {
            return $this->label;
        }
        return ucwords(str_replace('_', ' ', $this->id));
    }

    /**
     * Returns the label HTML element for the setting input.
     *
     * @return string
     */
    public function getLabelElement(): string
    {
        return '<label for="' . $this->input->getId() . '">' . $this->getTitle() . '</label>';
    }

    /**
     * Outputs the HTML input element.
     *
     * @param array{value: mixed} $args The arguments passed to {@see add_settings_field()}.
     * @return void
     */
    public function render(array $args): void
    {
        $this->input->setValue($args['value'] ?? Plugin::getInstance()->getSetting($this->id));
        echo $this->input;
    }

    /**
     * Coerces the value of the setting to the appropriate type.
     *
     * @param mixed $value The value to be coerced.
     * @return mixed The coerced value.
     */
    public function coerceValue(mixed $value): mixed
    {
        return match ($this->type) {
            SettingType::Boolean => Settings::toBoolean($value),
            SettingType::CheckBox => Settings::toStringArray($value),
            SettingType::Integer => Settings::toInteger($value),
            SettingType::Select, SettingType::Text => Settings::toString($value),
            SettingType::Skip => null,
        };
    }

    /**
     * Creates and returns the input object otherwise null if it should be skipped.
     *
     * @return InputInterface|null
     */
    private function createInput(): null|InputInterface
    {
        if ($this->type === SettingType::Skip) {
            return null;
        }
        $args = [
            'id' => 'rollbar_wp_' . $this->id,
            'name' => ['rollbar_wp', $this->id],
            'default' => $this->default,
            'label' => '',
            'helpText' => $this->helpText,
        ];
        foreach ($this->inputArgs as $key => $value) {
            if (!property_exists($this->type->value, $key) || array_key_exists($key, $args)) {
                continue;
            }
            $args[$key] = $value;
        }
        if (in_array($this->type, [SettingType::Select, SettingType::CheckBox])) {
            $args['options'] = $this->options;
        }
        return new $this->type->value(...$args);
    }
}
