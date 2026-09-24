<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white text-lga-navy antialiased">
        <header @class([
            'z-40 text-white',
            'absolute inset-x-0 top-0 bg-gradient-to-b from-black/50 to-transparent' => request()->routeIs('home'),
            'border-b border-zinc-200 bg-black' => ! request()->routeIs('home'),
        ])>
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
                <a href="{{ route('home') }}" class="flex shrink-0 items-center" wire:navigate>
                    <img
                        src="{{ \App\Support\Media::logoWhiteUrl() }}"
                        alt="Lagos Grassroots Alliance"
                        class="h-14 w-auto object-contain drop-shadow sm:h-20"
                    >
                </a>
                <nav class="flex flex-wrap items-center gap-3 text-sm">
                    <a href="{{ route('posts.index') }}" class="hover:text-lga-gold" wire:navigate>News</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="hover:text-lga-gold" wire:navigate>Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-lga-gold" wire:navigate>Log in</a>
                        <a href="{{ route('join') }}" class="btn-lga-primary px-4 py-2" wire:navigate>Join the Alliance</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer class="border-t border-zinc-200 bg-black text-zinc-300">
            <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-10 sm:px-6 md:flex-row md:items-center md:justify-between">
                <div>
                    <img src="{{ \App\Support\Media::logoWhiteUrl() }}" alt="" class="mb-3 h-12 w-auto">
                    <p class="text-sm">One Lagos, Many Voices · See the people. Know the streets. Grow the movement.</p>
                </div>
                <p class="text-sm">&copy; {{ date('Y') }} Lagos Grassroots Alliance</p>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
