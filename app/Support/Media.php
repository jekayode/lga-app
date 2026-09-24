<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class Media
{
    public static function diskName(): string
    {
        return (string) config('filesystems.media', 'public');
    }

    public static function disk(): Filesystem
    {
        return Storage::disk(self::diskName());
    }

    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return self::disk()->url($path);
    }

    public static function brandingUrl(string $key): ?string
    {
        $path = config("branding.{$key}");

        if (blank($path) || ! self::disk()->exists($path)) {
            return null;
        }

        return self::url($path);
    }

    public static function logoUrl(): string
    {
        return self::brandingUrl('logo')
            ?? asset('images/lga-logo.png');
    }

    public static function logoWhiteUrl(): string
    {
        return self::brandingUrl('logo_white')
            ?? asset('images/lga-logo-white.png');
    }

    public static function heroUrl(): string
    {
        return self::brandingUrl('hero')
            ?? asset('images/hero.jpg');
    }
}
