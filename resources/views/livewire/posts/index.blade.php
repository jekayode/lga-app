<div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
    <div class="text-center">
        <h1 class="text-3xl font-bold text-zinc-900 sm:text-4xl">Stay informed with the latest news</h1>
        <p class="mt-3 text-zinc-500">Read the latest updates and stories from the Alliance across Lagos.</p>
    </div>

    <div class="news-card-grid mt-12">
        @forelse ($posts as $post)
            <x-news-card :post="$post" />
        @empty
            <p class="col-span-full text-center text-zinc-500">{{ __('No posts published yet.') }}</p>
        @endforelse
    </div>

    <div class="mt-10">{{ $posts->links() }}</div>
</div>
