<?php
/**
 * @since 3.0.0
 *
 * @var array{
 *     type: "error"|"warning"|"success"|"info",
 *     message: string,
 * }[] $messages
 */

foreach ($messages as $message) : ?>
<div class="notice notice-<?= $message['type'] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Not user input. ?> is-dismissible">
    <p><?= $message['message'] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Not user input. ?></p>
</div>
<?php endforeach; ?>