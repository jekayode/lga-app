<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class NewsContentSeeder extends Seeder
{
    /**
     * Seed published news posts and gallery rows that point at existing R2 paths.
     *
     * Safe to re-run: updates by slug and replaces gallery images for each post.
     *
     *   php artisan db:seed --class=NewsContentSeeder --force
     */
    public function run(): void
    {
        $path = database_path('seeders/data/news_content.json');

        if (! File::exists($path)) {
            $this->command?->error("Missing news content file: {$path}");

            return;
        }

        /** @var array{category: array<string, mixed>, posts: list<array<string, mixed>>} $payload */
        $payload = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        $author = User::query()->where('role', Role::Admin)->orderBy('id')->first()
            ?? User::query()->orderBy('id')->first();

        if ($author === null) {
            $this->command?->error('No users found. Seed an admin user first (AdminUserSeeder).');

            return;
        }

        $categoryData = $payload['category'] ?? null;

        if (! is_array($categoryData) || blank($categoryData['slug'] ?? null)) {
            $this->command?->error('Invalid category payload in news_content.json.');

            return;
        }

        $category = Category::query()->updateOrCreate(
            ['slug' => $categoryData['slug']],
            [
                'name' => $categoryData['name'],
                'description' => $categoryData['description'] ?? null,
                'is_active' => (bool) ($categoryData['is_active'] ?? true),
            ],
        );

        $created = 0;
        $updated = 0;

        foreach ($payload['posts'] as $postData) {
            $existed = Post::query()->where('slug', $postData['slug'])->exists();

            $post = Post::query()->updateOrCreate(
                ['slug' => $postData['slug']],
                [
                    'category_id' => $category->id,
                    'author_id' => $author->id,
                    'title' => $postData['title'],
                    'content' => $postData['content'],
                    'featured_image_path' => $postData['featured_image_path'],
                    'published_at' => filled($postData['published_at'] ?? null)
                        ? Carbon::parse($postData['published_at'])
                        : now(),
                ],
            );

            $post->images()->delete();

            foreach ($postData['images'] ?? [] as $image) {
                PostImage::query()->create([
                    'post_id' => $post->id,
                    'path' => $image['path'],
                    'alt_text' => $image['alt_text'] ?? null,
                    'sort_order' => (int) ($image['sort_order'] ?? 0),
                ]);
            }

            $existed ? $updated++ : $created++;
        }

        $this->command?->info("News content seeded: {$created} created, {$updated} updated (author #{$author->id}).");
    }
}
