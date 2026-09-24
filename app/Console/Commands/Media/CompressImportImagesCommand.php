<?php

namespace App\Console\Commands\Media;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CompressImportImagesCommand extends Command
{
    protected $signature = 'media:compress-imports
                            {--max-width=1600 : Max width in pixels}
                            {--quality=78 : WebP quality 1-100}';

    protected $description = 'Compress raw Drive imports to WebP under storage/app/media-import/webp';

    public function handle(): int
    {
        if (! function_exists('imagewebp')) {
            $this->error('PHP GD WebP support is required.');

            return self::FAILURE;
        }

        $rawRoot = storage_path('app/media-import/raw');
        $webpRoot = storage_path('app/media-import/webp');
        $maxWidth = (int) $this->option('max-width');
        $quality = (int) $this->option('quality');

        if (! File::isDirectory($rawRoot)) {
            $this->error("Raw import folder missing: {$rawRoot}");

            return self::FAILURE;
        }

        $files = File::allFiles($rawRoot);
        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        $written = 0;

        foreach ($files as $file) {
            $bar->advance();

            if (! in_array(Str::lower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp'], true)) {
                continue;
            }

            $relative = Str::after($file->getPathname(), $rawRoot.DIRECTORY_SEPARATOR);
            $event = Str::before($relative, DIRECTORY_SEPARATOR) ?: 'misc';
            $targetDir = $webpRoot.DIRECTORY_SEPARATOR.$event;
            File::ensureDirectoryExists($targetDir);

            $target = $targetDir.DIRECTORY_SEPARATOR.Str::lower(pathinfo($file->getFilename(), PATHINFO_FILENAME)).'.webp';

            if ($this->compressToWebp($file->getPathname(), $target, $maxWidth, $quality)) {
                $written++;
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Wrote {$written} WebP files to {$webpRoot}");

        return self::SUCCESS;
    }

    protected function compressToWebp(string $source, string $target, int $maxWidth, int $quality): bool
    {
        $info = @getimagesize($source);

        if ($info === false) {
            return false;
        }

        [$width, $height] = $info;
        $type = $info[2];

        $image = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($source),
            IMAGETYPE_PNG => @imagecreatefrompng($source),
            IMAGETYPE_WEBP => @imagecreatefromwebp($source),
            default => false,
        };

        if ($image === false) {
            return false;
        }

        if ($width > $maxWidth) {
            $newHeight = (int) round($height * ($maxWidth / $width));
            $resized = imagecreatetruecolor($maxWidth, $newHeight);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }

        $ok = imagewebp($image, $target, $quality);
        imagedestroy($image);

        return $ok;
    }
}
