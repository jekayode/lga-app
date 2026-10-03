<?php

namespace App\Console\Commands\Media;

use App\Support\Media;
use Illuminate\Console\Command;
use Illuminate\Filesystem\AwsS3V3Adapter;

class ApplyCacheHeadersCommand extends Command
{
    protected $signature = 'media:cache-headers
                            {--dry-run : List what would change without updating objects}';

    protected $description = 'Set long-lived Cache-Control metadata on existing media objects (posts) and a shorter one on branding';

    public function handle(): int
    {
        $disk = Media::disk();

        if (! $disk instanceof AwsS3V3Adapter) {
            $this->error('The media disk is not S3/R2; cache headers only apply to object storage.');

            return self::FAILURE;
        }

        $client = $disk->getClient();
        $bucket = (string) config('filesystems.disks.'.Media::diskName().'.bucket');
        $policies = [
            'posts' => (string) config('filesystems.disks.'.Media::diskName().'.options.CacheControl'),
            'branding' => Media::BRANDING_CACHE_CONTROL,
        ];

        $updated = 0;

        foreach ($policies as $prefix => $cacheControl) {
            foreach ($disk->allFiles($prefix) as $key) {
                $head = $client->headObject(['Bucket' => $bucket, 'Key' => $key]);

                if (($head['CacheControl'] ?? null) === $cacheControl) {
                    continue;
                }

                $this->line("{$key} → {$cacheControl}");

                if (! $this->option('dry-run')) {
                    // Copying an object onto itself with REPLACE rewrites its metadata in place.
                    $client->copyObject([
                        'Bucket' => $bucket,
                        'Key' => $key,
                        'CopySource' => rawurlencode($bucket).'/'.str_replace('%2F', '/', rawurlencode($key)),
                        'MetadataDirective' => 'REPLACE',
                        'CacheControl' => $cacheControl,
                        'ContentType' => $head['ContentType'] ?? 'application/octet-stream',
                    ]);
                }

                $updated++;
            }
        }

        $this->info(($this->option('dry-run') ? 'Would update ' : 'Updated ').$updated.' object(s).');

        return self::SUCCESS;
    }
}
