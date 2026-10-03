<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
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

    /**
     * Resolve a branding asset URL, caching the remote existence check so R2 is not hit per request.
     */
    public static function brandingUrl(string $key): ?string
    {
        $path = config("branding.{$key}");

        if (blank($path)) {
            return null;
        }

        $url = Cache::memo()->remember(
            self::brandingCacheKey($key),
            now()->addDay(),
            fn (): string => self::disk()->exists($path) ? (string) self::url($path) : '',
        );

        return $url === '' ? null : $url;
    }

    public static function forgetBrandingUrls(): void
    {
        foreach (array_keys(config('branding', [])) as $key) {
            Cache::memo()->forget(self::brandingCacheKey($key));
        }
    }

    protected static function brandingCacheKey(string $key): string
    {
        return 'media.branding.'.self::diskName().'.'.$key;
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
