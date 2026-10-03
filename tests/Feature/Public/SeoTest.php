<?php

use App\Models\Post;

/**
 * @return array<string, mixed>
 */
function jsonLdGraph(string $html): array
{
    preg_match('#<script type="application/ld\+json">(.+?)</script>#s', $html, $matches);

    expect($matches)->not->toBeEmpty();

    return json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR)['@graph'];
}

test('homepage has indexable metadata and organization structured data', function () {
    $response = $this->get(route('home'))->assertOk();
    $html = $response->getContent();

    $response
        ->assertSee('<title>Lagos Grassroots Alliance — One Lagos, Many Voices</title>', false)
        ->assertSee('<meta name="description" content="'.e(config('seo.description')).'">', false)
        ->assertSee('<meta name="robots" content="index, follow, max-image-preview:large">', false)
        ->assertSee('<link rel="canonical" href="'.url('/').'">', false)
        ->assertSee('property="og:image"', false)
        ->assertSee('name="twitter:card" content="summary_large_image"', false);

    expect(collect(jsonLdGraph($html))->pluck('@type')->all())->toContain('Organization', 'WebSite');
});

test('news post uses its meta description and publishes article structured data', function () {
    $post = Post::factory()->create([
        'title' => 'Ikorodu General Assembly',
        'content' => '<p>Long article body that should not be used.</p>',
        'meta_description' => 'Highlights from the Ikorodu general assembly.',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get(route('posts.show', $post))->assertOk();

    $response
        ->assertSee('<title>Ikorodu General Assembly | Lagos Grassroots Alliance</title>', false)
        ->assertSee('<meta name="description" content="Highlights from the Ikorodu general assembly.">', false)
        ->assertSee('<meta property="og:type" content="article">', false)
        ->assertSee('<link rel="canonical" href="'.route('posts.show', $post).'">', false);

    $article = collect(jsonLdGraph($response->getContent()))->firstWhere('@type', 'NewsArticle');

    expect($article['headline'])->toBe('Ikorodu General Assembly')
        ->and($article['datePublished'])->toBe($post->published_at->toIso8601String());
});

test('news post falls back to a plain-text excerpt when no meta description is set', function () {
    $post = Post::factory()->create([
        'content' => '<p>Lagosians &amp; neighbours gathered <strong>together</strong>.</p>',
        'meta_description' => null,
        'published_at' => now()->subDay(),
    ]);

    expect($post->seoDescription())->toBe('Lagosians & neighbours gathered together.');
});

test('account pages are not indexed but the join page is', function () {
    $this->get(route('login'))->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    $this->get(route('join'))->assertSee('<meta name="robots" content="index, follow, max-image-preview:large">', false);
});

test('sitemap lists public pages and published posts only', function () {
    $published = Post::factory()->create(['published_at' => now()->subDay()]);
    $draft = Post::factory()->draft()->create();

    $this->get(route('seo.sitemap'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee(route('join'), false)
        ->assertSee(route('posts.show', $published), false)
        ->assertDontSee(route('posts.show', $draft), false);
});

test('llms.txt summarises the alliance and links published news', function () {
    $published = Post::factory()->create(['title' => 'Badagry Town Hall', 'published_at' => now()->subDay()]);
    Post::factory()->draft()->create(['title' => 'Unpublished Draft']);

    $this->get(route('seo.llms'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('# Lagos Grassroots Alliance', false)
        ->assertSee('[Badagry Town Hall]('.route('posts.show', $published).')', false)
        ->assertDontSee('Unpublished Draft');
});
