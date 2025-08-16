<?php
/**
 * @since 3.0.0
 *
 * @var CheckBoxInput $input
 */

use Rollbar\WordPress\Html\Input\CheckBoxInput;
use Rollbar\WordPress\Html\Template;

// Exit if accessed directly
defined('ABSPATH') || exit;

foreach ($input->getOptions() as $value => $label): ?>
<div>
    <input
        type="checkbox"
        name="<?= esc_attr($input->getName()) ?>[]"
        id="<?= esc_attr($input->getId()) ?>_<?= esc_attr($value) ?>"
        data-setting="<?= esc_attr($input->getId()) ?>"
        value="<?= esc_attr($value) ?>"
        <?= checked(in_array($value, $input->getValue()), display: false) ?>
        <?= disabled($input->isDisabled()) ?>
        <?= disabled($input->isDisabled()) ?>
    />
    <?php if (!empty($label)) : ?>
        <label for="<?= esc_attr($input->getId()) ?>_<?= esc_attr($value) ?>"><?= $label ?></label>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?= $input->showReset() ? Template::string(ROLLBAR_PLUGIN_DIR . '/templates/html/reset.php', ['input' => $input]) : '' ?>
<?php if (!empty($input->getHelpText())) : ?>
    <p class="description"><?= $input->getHelpText() ?></p>
<?php endif; ?>
