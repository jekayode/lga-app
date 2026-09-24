<?php

use App\Models\Post;

test('homepage shows published posts in a carousel and a view all link', function () {
    Post::factory()->count(5)->create([
        'published_at' => now()->subDay(),
    ]);

    Post::factory()->draft()->create([
        'title' => 'Hidden Draft Story',
    ]);

    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Stay informed with the latest news')
        ->assertSee('View all news')
        ->assertSee('news-carousel', false)
        ->assertDontSee('Hidden Draft Story');

    expect(substr_count($response->getContent(), 'group flex h-full flex-col'))->toBe(5);
});

test('news index lists published posts', function () {
    Post::factory()->create([
        'title' => 'Alliance Mobilises Across Lagos',
        'published_at' => now()->subHour(),
    ]);

    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSee('Alliance Mobilises Across Lagos');
});
