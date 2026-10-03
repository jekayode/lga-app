<?php

namespace App\Livewire\Posts;

use App\Models\Post;
use App\Support\Seo;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class Show extends Component
{
    public Post $post;

    public function mount(Post $post, Seo $seo): void
    {
        abort_unless($post->published_at !== null && $post->published_at->lte(now()), 404);
        $this->post = $post->load(['category', 'images', 'author']);

        $this->describeForSearch($seo);
    }

    protected function describeForSearch(Seo $seo): void
    {
        $post = $this->post;
        $url = route('posts.show', $post);
        $images = collect([$post->featuredImageUrl()])
            ->merge($post->images->take(3)->map(fn ($image) => $image->url()))
            ->filter()
            ->values()
            ->all();

        $seo->title($post->title)
            ->description($post->seoDescription())
            ->image($post->featuredImageUrl(), $post->title)
            ->type('article')
            ->canonical($url)
            ->indexable()
            ->openGraph('article:published_time', $post->published_at->toIso8601String())
            ->openGraph('article:modified_time', $post->updated_at->toIso8601String());

        if ($post->category) {
            $seo->openGraph('article:section', $post->category->name);
        }

        $seo->schema(Seo::organizationSchema())
            ->schema(array_filter([
                '@type' => 'NewsArticle',
                'headline' => Str::limit($post->title, 110, ''),
                'description' => $post->seoDescription(),
                'image' => $images ?: null,
                'datePublished' => $post->published_at->toIso8601String(),
                'dateModified' => $post->updated_at->toIso8601String(),
                'articleSection' => $post->category?->name,
                'inLanguage' => 'en-NG',
                'mainEntityOfPage' => $url,
                'author' => ['@id' => url('/').'/#organization'],
                'publisher' => ['@id' => url('/').'/#organization'],
            ]))
            ->schema([
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'News', 'item' => route('posts.index')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $post->title, 'item' => $url],
                ],
            ]);
    }

    public function render()
    {
        return view('livewire.posts.show')
            ->title($this->post->title);
    }
}
