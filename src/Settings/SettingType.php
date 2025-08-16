<?php

namespace Rollbar\WordPress\Settings;

use Rollbar\WordPress\Html\Input\BooleanInput;
use Rollbar\WordPress\Html\Input\CheckBoxInput;
use Rollbar\WordPress\Html\Input\NumberInput;
use Rollbar\WordPress\Html\Input\SelectInput;
use Rollbar\WordPress\Html\Input\TextInput;

// Exit if accessed directly
// phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
defined('ABSPATH') || exit;
// phpcs:enable

/**
 * The types of settings that can be used.
 *
 * @since 3.0.0
 */
enum SettingType: string
{
    case Boolean = BooleanInput::class;
    case CheckBox = CheckBoxInput::class;
    case Integer = NumberInput::class;
    case Select = SelectInput::class;
    case Skip = '';
    case Text = TextInput::class;
}
