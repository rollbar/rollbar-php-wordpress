<?php
/**
 * @since 3.0.0
 *
 * @var BooleanInput $input
 */

use Rollbar\WordPress\Html\Input\BooleanInput;
use Rollbar\WordPress\Html\Template;

// Exit if accessed directly
defined('ABSPATH') || exit;

?>
<input
    type="checkbox"
    name="<?= esc_attr($input->getName()) ?>"
    id="<?= esc_attr($input->getId()) ?>"
    data-setting="<?= esc_attr($input->getId()) ?>"
    value="1"
    <?= checked($input->getValue(), true, false) ?>
    <?= disabled($input->isDisabled()) ?>
    <?= $input->getHelpText() ? 'aria-describedby="' . esc_attr($input->getId()) . '-help"' : '' ?>
/>
<?= $input->showReset() ? Template::string(ROLLBAR_PLUGIN_DIR . '/templates/html/reset.php', ['input' => $input],
) : '' ?>
<?php if (!empty($input->getLabel())) : ?>
    <label for="<?= esc_attr($input->getId()) ?>"><?= $input->getLabel() ?></label>
<?php endif; ?>
<?php if (!empty($input->getHelpText())) : ?>
    <p class="description"><?= $input->getHelpText() ?></p>
<?php endif; ?>
