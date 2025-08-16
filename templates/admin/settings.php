<?php

use Rollbar\WordPress\Admin\FlashMessages;

// Exit if accessed directly
defined('ABSPATH') || exit;

?>
<div class="wrap">
    <h1 class="rollbar-header">Rollbar Settings</h1>
    <form action="<?= get_admin_url(path: '/options.php') ?>" method="post">
        <?= FlashMessages::flushMessages() ?>
        <?php
        settings_fields('rollbar_wp');
        do_settings_sections('rollbar_wp');
        submit_button();
        ?>
    </form>
</div>
<form action="<?= esc_url(admin_url('admin-post.php')); ?>" method="post">
    <input type="hidden" name="action" value="rollbar_wp_restore_defaults"/>
    <button
            type="submit"
            class="button button-secondary"
            name="restore-all-defaults"
            id="rollbar_wp_restore_all_defaults">
        Restore All Defaults
    </button>
</form>
<br/>
<button
        type="button"
        class="button button-secondary"
        name="test-logging"
        id="rollbar_wp_test_logging">
    Send test message to Rollbar
</button>
<div id="rollbar_test_message_container"></div>
