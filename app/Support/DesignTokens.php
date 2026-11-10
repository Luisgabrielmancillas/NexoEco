<?php

namespace App\Support;

use InvalidArgumentException;

/** Presentation tokens shared by web styles, maps and HTML email. */
class DesignTokens
{
    public static function color(string $name): string
    {
        static $colors;
        $colors ??= json_decode(file_get_contents(resource_path('design-system/tokens.json')), true, flags: JSON_THROW_ON_ERROR)['colors'];
        $value = $colors[$name] ?? throw new InvalidArgumentException("Unknown color token: {$name}");

        return str_starts_with($value, '{') ? self::color(substr($value, 1, -1)) : $value;
    }
}
