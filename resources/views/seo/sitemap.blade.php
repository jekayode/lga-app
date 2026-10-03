{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
@foreach ($pages as $page)
    <url>
        <loc>{{ $page['url'] }}</loc>
@if ($page['lastmod'])
        <lastmod>{{ $page['lastmod']->toAtomString() }}</lastmod>
@endif
        <priority>{{ $page['priority'] }}</priority>
    </url>
@endforeach
@foreach ($posts as $post)
    <url>
        <loc>{{ route('posts.show', $post) }}</loc>
        <lastmod>{{ $post->updated_at->toAtomString() }}</lastmod>
        <priority>0.7</priority>
@foreach (collect([$post->featuredImageUrl()])->merge($post->images->map(fn ($image) => $image->url()))->filter()->unique() as $imageUrl)
        <image:image><image:loc>{{ $imageUrl }}</image:loc></image:image>
@endforeach
    </url>
@endforeach
</urlset>
