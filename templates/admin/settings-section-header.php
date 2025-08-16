<?php
/**
 * @since 3.0.0
 *
 * @var string $id
 * @var string $title
 * @var string $description
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

?>
<hr>
<div class="rollbar-settings-section-header">
    <h2 id="<?= $id ?>" class="section-heading">
        <?= $title ?>
        <span class="dashicons dashicons-admin-collapse"></span>
    </h2>
    <?php if (!empty($description)) : ?>
        <div class="">
            <?= $description ?>
        </div>
    <?php endif; ?>
</div>
