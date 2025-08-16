<?php
/**
 * @since 3.0.0
 *
 * @var array{
 *     type: "error"|"warning"|"success"|"info",
 *     message: string,
 * }[] $messages
 */

echo 'foo';
foreach ($messages as $message) : ?>
<div class="notice notice-<?= $message['type'] ?> is-dismissible">
    <p><?= $message['message'] ?></p>
</div>
<?php endforeach; ?>