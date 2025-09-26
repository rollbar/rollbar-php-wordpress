<?php
/**
 * @since 3.0.0
 *
 * @var bool $php_logging_enabled
 * @var string $server_side_access_token
 * @var bool $js_logging_enabled
 * @var string $client_side_access_token
 */

use Rollbar\WordPress\Html\Input\BooleanInput;
use Rollbar\WordPress\Html\Input\TextInput;

echo new BooleanInput(
    id: 'rollbar_wp_php_logging_enabled',
    name: ['rollbar_wp', 'php_logging_enabled'],
    value: $php_logging_enabled,
    label: 'Turn on logging with PHP',
    showReset: false,
);
?>
<div id="rollbar_wp_server_side_access_token_container" class="hidden">
    <h4 style="margin: 15px 0 5px 0;">
        Server Side Access Token (post_server_item)
    </h4>
    <?= new TextInput(
        id: 'rollbar_wp_server_side_access_token',
        name: ['rollbar_wp', 'server_side_access_token'],
        value: $server_side_access_token,
        showReset: false,
        attributes: ['style' => 'width: 400px;'],
    ) ?>
    <p class="description">
        If no access token is provided here, the following will be used:
        <ol>
            <li>the <code>ROLLBAR_ACCESS_TOKEN</code> constant usually defined in your <code>wp-config.php</code></li>
            <li>the <code>ROLLBAR_ACCESS_TOKEN</code> server environment variable</li>
        </ol>
    </p>
</div>
<br/>
<?= new BooleanInput(
    id: 'rollbar_wp_js_logging_enabled',
    name: ['rollbar_wp', 'js_logging_enabled'],
    value: $js_logging_enabled,
    label: 'Turn on logging with JavaScript (with rollbar.js)',
    showReset: false,
); ?>
<div id="rollbar_wp_client_side_access_token_container" class="hidden">
    <h4 style="margin: 5px 0;">Client Side Access Token (post_client_item)
    </h4>
    <?= new TextInput(
        id: 'rollbar_wp_client_side_access_token',
        name: ['rollbar_wp', 'client_side_access_token'],
        value: $client_side_access_token,
        showReset: false,
        attributes: ['style' => 'width: 400px;'],
    ) ?>
</div>
<p class="description">
    You can find your access tokens under your project settings: <strong>Project Access Tokens</strong>.
</p>