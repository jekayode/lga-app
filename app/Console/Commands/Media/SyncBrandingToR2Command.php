<?php

namespace App\Console\Commands\Media;

use App\Support\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SyncBrandingToR2Command extends Command
{
    protected $signature = 'media:sync-branding
                            {--force : Overwrite existing remote files}';

    protected $description = 'Upload logo and hero images to the configured media disk (R2 or public)';

    public function handle(): int
    {
        $disk = Media::disk();
        $diskName = Media::diskName();

        $this->info("Using media disk: {$diskName}");

        $map = [
            public_path('images/lga-logo.png') => config('branding.logo'),
            public_path('images/lga-logo-white.png') => config('branding.logo_white'),
            public_path('images/hero.jpg') => config('branding.hero'),
        ];

        foreach ($map as $local => $remote) {
            if (! File::exists($local)) {
                $this->error("Missing local file: {$local}");

                continue;
            }

            if ($disk->exists($remote) && ! $this->option('force')) {
                $this->line("Skip (exists): {$remote}");

                continue;
            }

            if (Str::endsWith($remote, '.webp') && ! Str::endsWith(Str::lower($local), '.webp')) {
                $temp = storage_path('app/media-import/branding/'.basename($remote));
                File::ensureDirectoryExists(dirname($temp));

                if (! $this->convertToWebp($local, $temp)) {
                    $this->error("Failed to convert {$local} to WebP");

                    continue;
                }

                $contents = File::get($temp);
            } else {
                $contents = File::get($local);
            }

            $disk->put($remote, $contents);
            $this->info("Uploaded: {$remote} → ".$disk->url($remote));
        }

        $this->newLine();
        $this->table(['Asset', 'URL'], [
            ['Logo', Media::logoUrl()],
            ['Logo white', Media::logoWhiteUrl()],
            ['Hero', Media::heroUrl()],
        ]);

        return self::SUCCESS;
    }

    protected function convertToWebp(string $source, string $target): bool
    {
        if (! function_exists('imagewebp')) {
            return false;
        }

        $info = @getimagesize($source);

        if ($info === false) {
            return false;
        }

        $image = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($source),
            IMAGETYPE_PNG => @imagecreatefrompng($source),
            IMAGETYPE_WEBP => @imagecreatefromwebp($source),
            default => false,
        };

        if ($image === false) {
            return false;
        }

        [$width, $height] = $info;
        $maxWidth = 1920;

        if ($width > $maxWidth) {
            $newHeight = (int) round($height * ($maxWidth / $width));
            $resized = imagecreatetruecolor($maxWidth, $newHeight);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }

        $ok = imagewebp($image, $target, 80);
        imagedestroy($image);

        return $ok;
    }
}
