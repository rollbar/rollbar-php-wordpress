<?php
/**
 * @since 3.0.0
 *
 * @var AbstractInput $input
 */

use Rollbar\WordPress\Html\Input\AbstractInput;
use Rollbar\WordPress\Settings\SettingType;

// Exit if accessed directly
defined('ABSPATH') || exit;

$default = $input->getDefault();
if ($input::class === SettingType::Boolean->value) {
    $default = $default ? 'true' : 'false';
}
if (is_array($default)) {
    $default = implode(',', $default);
}
?>
<button
    type="button"
    class="button button-secondary rollbar_wp_restore_default"
    name="restore-default"
    data-setting="<?= $input->getId(); ?>"
    data-setting-input-type="<?= $input->getType(); ?>"
    data-default="<?= $default; ?>">
    Reset
</button>
