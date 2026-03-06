<?php

namespace Rollbar\WordPress\BuildUtils;

use PhpToken;
use RuntimeException;

/**
 * Fixes namespace strings in PHP files that PhpScoper might have missed.
 *
 * @since 3.1.2
 */
class NamespaceTokenFixer
{
    /**
     * The tokens of the file contents.
     *
     * @var PhpToken[] $tokens
     */
    public array $tokens = [];

    /**
     * Constructor.
     *
     * @param string $filename The path and filename of the file to fix.
     * @param string $contents The contents of the file to fix.
     */
    public function __construct(public string $filename = '', string $contents = '')
    {
        $this->tokens = PhpToken::tokenize($contents);
    }

    /**
     * Configures the file to fix.
     *
     * @param string $filename The path and filename of the file to fix.
     * @param string $contents The contents of the file to fix.
     * @return void
     */
    public function configure(string $filename, string $contents): void
    {
        $this->filename = $filename;
        $this->tokens = PhpToken::tokenize($contents);
    }

    /**
     * Checks if the file is fixable.
     *
     * @param string $path The path to the file.
     * @return bool
     */
    public function fileIsFixable(string $path): bool
    {
        foreach ($this->fixes() as $fixablePath => $fixes) {
            if (str_ends_with($path, $fixablePath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fixes the file.
     *
     * @return string The fixed contents of the file.
     */
    public function fix(): string
    {
        foreach ($this->fixes() as $fixablePath => $fixFunctions) {
            if (str_ends_with($this->filename, $fixablePath)) {
                foreach ($fixFunctions as $fixFunction) {
                    $fixFunction();
                }
            }
        }

        return implode('', $this->tokens);
    }

    /**
     * Returns an array of fixes for the file.
     *
     * @return array<string, array<callable>>
     */
    private function fixes(): array
    {
        return [
            '/build/vendor/composer/autoload_static.php' => [
                $this->fixAutoloadStaticPrefixLengthsPsr4(...),
                $this->fixUnscopedNamespaceStrings(...),
            ],
            '/build/vendor/composer/autoload_real.php' => [
                $this->fixUnscopedNamespaceStrings(...),
            ],
            '/build/vendor/composer/autoload_psr4.php' => [
                $this->fixUnscopedNamespaceStrings(...),
            ],
        ];
    }

    /**
     * Fixes the `$prefixLengthsPsr4` property in the `autoload_static.php` file.
     *
     * @return void
     *
     * @throws RuntimeException If the `$prefixLengthsPsr4` variable could not be found.
     */
    private function fixAutoloadStaticPrefixLengthsPsr4(): void
    {
        // Collect all tokens belonging to the `$prefixLengthsPsr4` variable expression.
        $startIndex = -1;
        $endIndex = -1;
        $tokens = [];
        foreach ($this->tokens as $i => $token) {
            if (T_VARIABLE === $token->id && '$prefixLengthsPsr4' === (string)$token && -1 === $startIndex) {
                $startIndex = $i;
                continue;
            }
            if (-1 !== $startIndex && -1 === $endIndex) {
                if (59 === $token->id) { // ";" character
                    $endIndex = $i;
                    continue;
                }
                $tokens[] = $token;
            }
        }

        if (-1 === $endIndex) {
            throw new RuntimeException('Could not find $prefixLengthsPsr4 variable in autoload_static.php');
        }

        // Extract all namespace strings from the tokens.
        $namespaces = [];
        foreach ($tokens as $token) {
            //
            if (T_CONSTANT_ENCAPSED_STRING !== $token->id) {
                continue;
            }
            $string = (string)$token;
            // The first level keys of the array are always single characters (such as 'R' for 'Rollbar\'). Skip them.
            if (strlen($string) < 3) {
                continue;
            }
            // Remove quotes from the token string.
            $string = substr($token, 1, -1);
            // Skip strings that don't contain a backslash.
            if (!str_contains($string, '\\')) {
                continue;
            }
            // Fix escaped backslashes.
            $string = str_replace('\\\\', '\\', $string);

            // Prepend "RollbarWP\\" to all namespace strings that are not already correctly prefixed.
            if (!str_starts_with($string, 'Rollbar\\') && !str_starts_with($string, 'RollbarWP\\')) {
                $string = 'RollbarWP\\' . $string;
            }
            $namespaces[] = $string;
        }

        $prefixLengthsPsr4 = [];

        foreach ($namespaces as $namespace) {
            $firstChar = substr($namespace, 0, 1);
            $prefixLengthsPsr4[$firstChar][$namespace] = strlen($namespace);
        }

        $result = var_export($prefixLengthsPsr4, true);

        $newTokens = PhpToken::tokenize('<?php $prefixLengthsPsr4 = ' . $result . ';');

        $newTokens = array_slice($newTokens, 1);

        array_splice(
            $this->tokens,
            $startIndex,
            $endIndex - $startIndex + 1,
            $newTokens,
        );
    }

    /**
     * Fixes unscoped namespace strings in the file.
     *
     * @return void
     */
    private function fixUnscopedNamespaceStrings(): void
    {
        foreach ($this->tokens as $i => $token) {
            if (T_CONSTANT_ENCAPSED_STRING !== $token->id) {
                continue;
            }
            // Cast the token to a string to get the token value.
            $string = (string)$token;
            // Remove quotes from the token string.
            $string = substr($string, 1, -1);
            if (!str_contains($string, '\\')) {
                continue;
            }
            // Skip strings that are already prefixed.
            if (str_starts_with($string, 'Rollbar\\') || str_starts_with($string, 'RollbarWP\\')) {
                continue;
            }
            // Ensure the first character is uppercase.
            $firstChar = substr($string, 0, 1);
            if (!ctype_upper($firstChar)) {
                continue;
            }
            $newString = '\'RollbarWP\\' . $string . '\'';
            $this->tokens[$i] = new PhpToken(T_CONSTANT_ENCAPSED_STRING, $newString, $token->line, $token->pos);
        }
    }
}
