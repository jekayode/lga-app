@props(['post'])

<a
    href="{{ route('posts.show', $post) }}"
    {{ $attributes->class('group flex h-full flex-col') }}
    wire:navigate
>
    <div class="overflow-hidden rounded-xl bg-zinc-100">
        @if ($post->featuredImageUrl())
            <img
                src="{{ $post->featuredImageUrl() }}"
                alt=""
                class="aspect-[16/10] w-full object-cover transition duration-300 group-hover:scale-[1.02]"
            >
        @else
            <div class="flex aspect-[16/10] w-full items-center justify-center bg-gradient-to-br from-lga-navy to-lga-green text-lg font-bold text-white">
                LGA
            </div>
        @endif
    </div>

    <div class="flex flex-1 flex-col pt-1">
        <p class="mt-4 text-xs font-medium uppercase tracking-wider text-zinc-400">
            {{ $post->published_at?->format('d M. Y') }}
        </p>

        <h3 class="mt-2 text-lg font-bold leading-snug text-zinc-900 transition group-hover:underline">
            {{ $post->title }}
        </h3>

        <div class="mt-auto flex flex-wrap items-center gap-3 pt-4">
            @if ($post->category)
                <span class="rounded bg-lga-green px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white">
                    {{ $post->category->name }}
                </span>
            @endif
            <span class="inline-flex items-center gap-1.5 text-xs text-zinc-500">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-3.5" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd" />
                </svg>
                {{ __(':mins MINS READ', ['mins' => $post->readingTimeMinutes()]) }}
            </span>
        </div>
    </div>
</a>
