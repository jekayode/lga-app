<?php

namespace App\Console\Commands\Media;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\User;
use App\Support\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportEventGalleriesCommand extends Command
{
    protected $signature = 'media:import-galleries
                            {--limit=24 : Max gallery images per event}
                            {--dry-run : Do not write to disk or database}';

    protected $description = 'Upload compressed event WebPs to the media disk and create/update gallery posts';

    /**
     * @var array<string, array{title: string, slug: string, body: string, published_offset_minutes: int}>
     */
    protected array $events = [
        'ikorodu' => [
            'title' => 'Lagos East General Assembly — Ikorodu',
            'slug' => 'lagos-east-general-assembly-ikorodu-gallery',
            'body' => '<p>Scenes from the Lagos Grassroots Alliance Lagos East General Assembly and inauguration of local managers in Ikorodu — community leaders and grassroots mobilisers gathering under the theme <em>“Building the Grassroots, Building the People.”</em></p><p>These moments capture the Alliance’s vision of <em>“One Lagos, Many Voices”</em> in action across Lagos East.</p>',
            'published_offset_minutes' => 3,
        ],
        'apapa-iganmu' => [
            'title' => 'Lagos Central General Assembly — Apapa/Iganmu',
            'slug' => 'lagos-central-general-assembly-apapa-iganmu-gallery',
            'body' => '<p>Highlights from the Lagos Grassroots Alliance Lagos Central General Assembly and inauguration of local managers at Apapa/Iganmu — stakeholders and community leaders strengthening grassroots participation.</p><p>Held in the spirit of <em>“Securing the Future, Through Innovation &amp; Participation,”</em> and the Alliance vision of <em>“One Lagos, Many Voices.”</em></p>',
            'published_offset_minutes' => 2,
        ],
        'general' => [
            'title' => 'Lagos Grassroots Alliance in the field',
            'slug' => 'lagos-grassroots-alliance-field-gallery',
            'body' => '<p>A look at Lagos Grassroots Alliance gatherings across the city — organisers, local managers and community members building one Lagos with many voices.</p>',
            'published_offset_minutes' => 1,
        ],
    ];

    public function handle(): int
    {
        $webpRoot = storage_path('app/media-import/webp');
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');
        $disk = Media::disk();

        $this->info('Media disk: '.Media::diskName());

        if (! File::isDirectory($webpRoot)) {
            $this->error("Run php artisan media:compress-imports first. Missing: {$webpRoot}");

            return self::FAILURE;
        }

        $category = Category::query()->firstOrCreate(
            ['slug' => 'events'],
            ['name' => 'Events', 'description' => 'Alliance gatherings', 'is_active' => true],
        );

        $authorId = User::query()->where('role', 'admin')->value('id');

        if (! $authorId) {
            $this->error('No admin user found to author posts.');

            return self::FAILURE;
        }

        foreach ($this->events as $folder => $meta) {
            $dir = $webpRoot.DIRECTORY_SEPARATOR.$folder;

            if (! File::isDirectory($dir)) {
                $this->warn("Skip missing folder: {$folder}");

                continue;
            }

            $files = collect(File::files($dir))
                ->filter(fn ($f) => Str::lower($f->getExtension()) === 'webp')
                ->sortBy(fn ($f) => $f->getFilename())
                ->take($limit)
                ->values();

            if ($files->isEmpty()) {
                $this->warn("No WebP files in {$folder}");

                continue;
            }

            $this->info("{$folder}: {$files->count()} images");

            if ($dryRun) {
                continue;
            }

            $featuredRemote = "posts/featured/{$meta['slug']}.webp";
            $disk->put($featuredRemote, File::get($files->first()->getPathname()));

            $post = Post::query()->updateOrCreate(
                ['slug' => $meta['slug']],
                [
                    'category_id' => $category->id,
                    'author_id' => $authorId,
                    'title' => $meta['title'],
                    'content' => $meta['body'],
                    'featured_image_path' => $featuredRemote,
                    'published_at' => now()->subMinutes($meta['published_offset_minutes']),
                ],
            );

            $post->images()->delete();

            foreach ($files as $index => $file) {
                $remote = sprintf('posts/gallery/%s/%02d.webp', $meta['slug'], $index + 1);
                $disk->put($remote, File::get($file->getPathname()));

                PostImage::query()->create([
                    'post_id' => $post->id,
                    'path' => $remote,
                    'alt_text' => $meta['title'].' — photo '.($index + 1),
                    'sort_order' => $index,
                ]);
            }

            $this->info("Post ready: /news/{$post->slug}");
        }

        return self::SUCCESS;
    }
}
