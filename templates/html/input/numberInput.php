<?php
/**
 * @since 3.0.0
 *
 * @var NumberInput $input
 */

use Rollbar\WordPress\Html\Input\NumberInput;
use Rollbar\WordPress\Html\Template;

// Exit if accessed directly
defined('ABSPATH') || exit;
?>
<input
    type="number"
    name="<?= esc_attr($input->getName()) ?>"
    id="<?= esc_attr($input->getId()) ?>"
    data-setting="<?= esc_attr($input->getId()) ?>"
    value="<?= esc_attr($input->getValue()) ?>"
    <?= disabled($input->isDisabled()) ?>
    <?= $input->getHelpText() ? 'aria-describedby="' . esc_attr($input->getId()) . '-help"' : '' ?>
    <?= $input->serializeAttributes() ?>
/>
<?= $input->showReset() ? Template::string(ROLLBAR_PLUGIN_DIR . '/templates/html/reset.php', ['input' => $input]) : '' ?>
<?php if (!empty($input->getLabel())) : ?>
    <label for="<?= esc_attr($input->getId()) ?>"><?= $input->getLabel() ?></label>
<?php endif; ?>
<?php if (!empty($input->getHelpText())) : ?>
    <p class="description"><?= $input->getHelpText() ?></p>
<?php endif; ?>
