<?php
/**
 * @since 3.0.0
 *
 * @var SelectInput $input
 */

use Rollbar\WordPress\Html\Input\SelectInput;
use Rollbar\WordPress\Html\Template;

// Exit if accessed directly
defined('ABSPATH') || exit;
?>
<select
    name="<?= esc_attr($input->getName()) ?>"
    id="<?= esc_attr($input->getId()) ?>"
    data-setting="<?= esc_attr($input->getId()) ?>"
    <?= disabled($input->isDisabled()) ?>
    <?= $input->getHelpText() ? 'aria-describedby="' . esc_attr($input->getId()) . '-help"' : '' ?>
    <?= $input->serializeAttributes() ?>
    class="regular-text"
>
    <?php foreach ($input->getOptions() as $value => $label) : ?> : ?>
        <option value="<?= esc_attr($value) ?>" <?= selected($value, $input->getValue()) ?>>
            <?= esc_html($label) ?>
        </option>
    <?php endforeach; ?>
</select>
<?= $input->showReset() ? Template::string(ROLLBAR_PLUGIN_DIR . '/templates/html/reset.php', ['input' => $input]) : '' ?>
<?php if (!empty($input->getLabel())) : ?>
    <label for="<?= esc_attr($input->getId()) ?>"><?= $input->getLabel() ?></label>
<?php endif; ?>
<?php if (!empty($input->getHelpText())) : ?>
    <p class="description"><?= $input->getHelpText() ?></p>
<?php endif; ?>
