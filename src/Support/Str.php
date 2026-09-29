<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Support;

/**
 * String helpers mirroring the functions published in the CAPI appendix.
 */
final class Str
{
    private function __construct()
    {
    }

    /**
     * Turn an item name into the slug Colnect uses inside picture URLs.
     *
     * This is a port of the urlize() function from the CAPI specification:
     * HTML entities are decoded, invalid characters are stripped and any
     * sequence of whitespace collapses into a single underscore.
     */
    public static function urlize(string $value): string
    {
        $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
        $value = (string) preg_replace('/&[^;]+;/', '_', $value);
        $value = str_replace(
            ['.', '"', '>', '<', '\\', ':', '/', '?', '#', '[', ']', '@', '!', '$', '&', '\'', '(', ')', '*', '+', ',', ';', '='],
            '',
            $value
        );
        $value = (string) preg_replace('/[\s_]+/', '_', $value);

        return trim($value, '_');
    }
}
