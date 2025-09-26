<?php

namespace Rollbar\WordPress\Html\Input;

use Stringable;

// Exit if accessed directly
// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
defined('ABSPATH') || exit;
// phpcs:enable

/**
 * Input Interface
 *
 * This interface defines the methods that all input types must implement.
 *
 * @since 3.0.0
 */
interface InputInterface extends Stringable
{
    /**
     * Returns the ID of the input.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Returns the value of the HTML name attribute.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns the value of the input.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Sets the value of the input.
     *
     * @param mixed $value
     */
    public function setValue($value): void;

    /**
     * Returns the default value of the input.
     *
     * @return mixed
     */
    public function getDefault();

    /**
     * Returns the label of the input.
     *
     * @return string
     */
    public function getLabel(): string;

    /**
     * Returns the help text to be displayed with the input.
     *
     * @return string
     */
    public function getHelpText(): string;

    /**
     * Returns the type of the input.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Returns a list of HTML attributes to be applied to the input.
     *
     * @return array<string, string>
     */
    public function getAttributes(): array;

    /**
     * Returns true if the input is disabled, false otherwise.
     *
     * @return bool
     */
    public function isDisabled(): bool;

    /**
     * Returns the serialized HTML attributes of the input.
     *
     * @return string
     */
    public function serializeAttributes(): string;

    /**
     * Returns true if the reset button should be displayed, false otherwise.
     *
     * @return bool
     */
    public function showReset(): bool;
}
