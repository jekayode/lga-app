<article class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
    @if ($post->category)
        <p class="text-sm font-semibold uppercase tracking-wide text-lga-green">{{ $post->category->name }}</p>
    @endif
    <h1 class="mt-2 text-4xl font-bold text-lga-navy">{{ $post->title }}</h1>
    <p class="mt-3 text-sm text-zinc-500">{{ $post->published_at?->format('j F Y') }}</p>

    @if ($post->featuredImageUrl())
        <img src="{{ $post->featuredImageUrl() }}" alt="" class="mt-8 w-full rounded-2xl object-cover">
    @endif

    <div class="post-body mt-8">
        {!! $post->content !!}
    </div>

    @if ($post->images->isNotEmpty())
        <div
            class="mt-10"
            x-data="{
                open: false,
                index: 0,
                images: {{ Js::from($post->images->map(fn ($image) => [
                    'src' => $image->url(),
                    'alt' => $image->alt_text ?: $post->title,
                ])->values()) }},
                show(i) {
                    this.index = i;
                    this.open = true;
                    document.body.classList.add('overflow-hidden');
                },
                close() {
                    this.open = false;
                    document.body.classList.remove('overflow-hidden');
                },
                next() {
                    this.index = (this.index + 1) % this.images.length;
                },
                prev() {
                    this.index = (this.index - 1 + this.images.length) % this.images.length;
                },
            }"
            @keydown.escape.window="open && close()"
            @keydown.arrow-right.window="open && next()"
            @keydown.arrow-left.window="open && prev()"
        >
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($post->images as $index => $image)
                    <button
                        type="button"
                        class="group block overflow-hidden rounded-xl text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-lga-green"
                        @click="show({{ $index }})"
                    >
                        <img
                            src="{{ $image->url() }}"
                            alt="{{ $image->alt_text }}"
                            class="aspect-[4/3] w-full object-cover transition duration-300 group-hover:scale-[1.02]"
                            loading="lazy"
                        >
                    </button>
                @endforeach
            </div>

            <template x-teleport="body">
                <div
                    x-show="open"
                    x-cloak
                    x-transition.opacity.duration.200ms
                    class="fixed inset-0 z-[100] flex flex-col bg-black/95"
                    role="dialog"
                    aria-modal="true"
                    aria-label="{{ __('Image gallery') }}"
                    @click.self="close()"
                >
                    <div class="flex shrink-0 items-center justify-between px-4 py-3 sm:px-6">
                        <p class="text-sm font-medium text-white/80" x-text="(index + 1) + ' / ' + images.length"></p>
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-zinc-900 shadow-lg transition hover:bg-zinc-100"
                            @click="close()"
                            aria-label="{{ __('Close') }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="size-5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                            {{ __('Close') }}
                        </button>
                    </div>

                    <div class="flex min-h-0 flex-1 items-center gap-2 px-3 sm:gap-4 sm:px-6" @click.self="close()">
                        <button
                            type="button"
                            class="inline-flex size-12 shrink-0 items-center justify-center rounded-full bg-white text-zinc-900 shadow-lg transition hover:bg-zinc-100 disabled:invisible sm:size-14"
                            @click.stop="prev()"
                            aria-label="{{ __('Previous image') }}"
                            :disabled="images.length < 2"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="size-7" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                        </button>

                        <div class="flex min-w-0 flex-1 items-center justify-center" @click.self="close()">
                            <img
                                :src="images[index].src"
                                :alt="images[index].alt"
                                class="max-h-[70vh] w-auto max-w-full rounded-lg object-contain shadow-2xl"
                                @click.stop
                            >
                        </div>

                        <button
                            type="button"
                            class="inline-flex size-12 shrink-0 items-center justify-center rounded-full bg-white text-zinc-900 shadow-lg transition hover:bg-zinc-100 disabled:invisible sm:size-14"
                            @click.stop="next()"
                            aria-label="{{ __('Next image') }}"
                            :disabled="images.length < 2"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="size-7" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex shrink-0 items-center justify-center gap-3 px-4 py-4 sm:gap-4">
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-zinc-900 shadow-lg transition hover:bg-zinc-100 disabled:invisible"
                            @click.stop="prev()"
                            :disabled="images.length < 2"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="size-5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                            {{ __('Previous') }}
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-zinc-900 shadow-lg transition hover:bg-zinc-100 disabled:invisible"
                            @click.stop="next()"
                            :disabled="images.length < 2"
                        >
                            {{ __('Next') }}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="size-5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    @endif
</article>
