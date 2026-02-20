<?php

namespace Rollbar\WordPress\Tests\utils;

use PHPUnit\Framework\TestCase;
use Rollbar\WordPress\BuildUtils\NamespaceTokenFixer;

class NamespaceTokenFixerTest extends TestCase
{
    private NamespaceTokenFixer $subject;

    public function setUp(): void
    {
        $this->subject = new NamespaceTokenFixer();
    }

    public function testFixUnscopedNamespaceStringsNoOp(): void
    {
        $input = '<?php $foo = "test";';

        $this->subject->configure('./build/vendor/composer/autoload_psr4.php', $input);

        self::assertSame($input, $this->subject->fix());
    }

    public function testFixUnscopedNamespaceStringsUnprefixed(): void
    {
        // Because we have a string within a string, we need two levels of escaping. It is ugly, but it produces the
        // equivalent of the following PHP code:
        // <?php $foo = 'Some\Namespace\\';
        $input = '<?php $foo = \'Some\Namespace\\\\\';';
        $this->subject->configure('./build/vendor/composer/autoload_psr4.php', $input);

        self::assertSame('<?php $foo = \'RollbarWP\Some\Namespace\\\\\';', $this->subject->fix());
    }

    public function testFixUnscopedNamespaceStringsPrefixed(): void
    {
        $input = '<?php $foo = \'RollbarWP\Some\Namespace\\\\\';';
        $this->subject->configure('./build/vendor/composer/autoload_psr4.php', $input);

        self::assertSame('<?php $foo = \'RollbarWP\Some\Namespace\\\\\';', $this->subject->fix());
    }

    public function testFixUnscopedNamespaceStringsInvalidFile(): void
    {
        $input = '<?php $foo = \'Some\Namespace\\\\\';';
        $this->subject->configure('foo.php', $input);

        self::assertSame('<?php $foo = \'Some\Namespace\\\\\';', $this->subject->fix());
    }

    public function testFixAutoloadStaticPrefixLengthsPsr4(): void
    {
        $input = '<?php $prefixLengthsPsr4 = array(
            \'R\' => array(\'Rollbar\\\\WordPress\\\\\' => 18, \'Rollbar\\\\\' => 8),
            \'P\' => array(\'Psr\\\\Log\\\\\' => 8),
            \'M\' => array(\'Monolog\\\\\' => 8),
        );
        $foo = 42;';

        $expected = '<?php $prefixLengthsPsr4 = array(
            \'R\' => array(
                \'Rollbar\\\\WordPress\\\\\' => 18, 
                \'Rollbar\\\\\' => 8, 
                \'RollbarWP\\\\Psr\\\\Log\\\\\' => 18, 
                \'RollbarWP\\\\Monolog\\\\\' => 18,
            ),
        );
        $foo = 42;';

        $this->subject->configure('./build/vendor/composer/autoload_static.php', $input);

        self::assertSame(self::normalizeString($expected), self::normalizeString($this->subject->fix()));
    }

    /**
     * Removes all whitespace from a string.
     *
     * @param string $string The string to normalize.
     * @return string
     */
    private static function normalizeString(string $string): string
    {
        return str_replace([' ', "\n", "\r", "\t"], '', $string);
    }
}
