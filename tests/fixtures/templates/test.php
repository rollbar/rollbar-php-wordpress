<?php
/**
 * A simple template for testing.
 *
 * @var string $foo
 * @var string[] $bar
 */

?>
<h1><?= $foo ?></h1>
<ul>
    <?php foreach ($bar as $item) : ?>
        <li><?= $item ?></li>
    <?php endforeach; ?>
</ul>
