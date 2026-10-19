<?php

namespace App\Support;

use Illuminate\Support\Str;

class MarketplaceImage
{
    public static function url(?string $path): ?string
    {
        $path = trim($path ?? '');
        if ($path === '') {
            return null;
        }
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }
        if (Str::startsWith($path, ['/', 'storage/'])) {
            return asset(ltrim($path, '/'));
        }

        return asset('storage/'.ltrim($path, '/'));
    }
}
