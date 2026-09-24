<x-layouts::public>
    <section class="relative isolate flex min-h-screen items-center overflow-hidden bg-[#0b2a1c] text-white">
        <div
            class="absolute inset-0"
            style="background-image:
                linear-gradient(100deg, rgba(4,20,14,0.94) 0%, rgba(9,42,28,0.82) 42%, rgba(7,125,76,0.35) 100%),
                url('{{ \App\Support\Media::heroUrl() }}');
                background-size: cover, cover;
                background-position: center, center;
                background-repeat: no-repeat, no-repeat;"
            aria-hidden="true"
        ></div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-5 pb-24 pt-44 sm:px-6">
            <div class="max-w-xl lg:max-w-2xl">
                <h1 class="mt-6 text-5xl font-extrabold leading-[1.05] tracking-tight text-white sm:text-7xl">
                    One Lagos,<br><span class="text-lga-mint">Many Voices.</span>
                </h1>
                <p class="mt-6 max-w-xl text-lg text-white/85 sm:text-2xl">
                    When those voices come together, they become impossible to ignore.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('join') }}" class="btn-lga-primary" wire:navigate>Join the Alliance</a>
                    <a href="{{ route('become-agent') }}" class="btn-lga-secondary-light" wire:navigate>Become an Agent</a>
                </div>
                <p class="mt-6 text-sm text-zinc-300">Footprints in all 20 Local Governments · Everyone welcome · No one left behind</p>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
        <h2 class="text-3xl font-bold text-lga-navy">Lagos belongs to all of us.</h2>
        <p class="mt-4 max-w-3xl text-lg text-zinc-700">
            One voice is easy to overlook. A million voices, moving together, change everything.
        </p>
        <p class="mt-4 max-w-3xl text-zinc-700">
            The Lagos Grassroots Alliance is not a party and not a podium — it's <strong>the people</strong>.
            Traders and students, drivers and doctors, from Ikoyi to Ikorodu, from Epe to Badagry.
            We are coming together as one strong voice to build a stronger Lagos.
        </p>
        <a href="{{ route('join') }}" class="btn-lga-primary mt-8" wire:navigate>Join the movement</a>
    </section>

    <section class="bg-zinc-50">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <h2 class="text-3xl font-bold text-lga-navy">When you join, you're never standing alone.</h2>
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    [
                        'title' => 'One voice, many people',
                        'body' => 'Stand shoulder-to-shoulder with Lagosians who love this city as much as you do.',
                        'icon' => 'users',
                    ],
                    [
                        'title' => 'Protect our votes',
                        'body' => 'Every vote is a voice. Together we make sure ours are counted and respected.',
                        'icon' => 'shield',
                    ],
                    [
                        'title' => 'Stronger institutions',
                        'body' => 'We push for a Lagos that works — fair, accountable, and built to last.',
                        'icon' => 'building',
                    ],
                    [
                        'title' => 'Make sure we\'re heard',
                        'body' => 'Alone, a concern is a complaint. Together, it\'s a movement leaders can\'t ignore.',
                        'icon' => 'megaphone',
                    ],
                ] as $card)
                    <div class="group rounded-2xl border border-zinc-100 bg-white p-8 text-center shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                        <div class="mx-auto mb-5 flex size-14 items-center justify-center text-lga-green transition group-hover:scale-110">
                            @if ($card['icon'] === 'users')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-12" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                </svg>
                            @elseif ($card['icon'] === 'shield')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-12" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                </svg>
                            @elseif ($card['icon'] === 'building')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-12" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75v3M21 10.5V6.75a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75v3.75" />
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-12" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.582.32c-.7.386-1.523-.15-1.425-.95A24.73 24.73 0 0 1 8.25 12c0-1.823.247-3.59.705-5.274.135-.6.958-1.136 1.425-.95l.582.32c.524.288.71.961.463 1.511a20.97 20.97 0 0 0-.985 2.783m0 9.18v-9.18m0 0a24.7 24.7 0 0 1 3.76-.43 24.706 24.706 0 0 1 3.76.43m-7.52 0v9.18m7.52-9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.582.32c-.7.386-1.523-.15-1.425-.95A24.73 24.73 0 0 1 15.75 12c0-1.823.247-3.59.705-5.274.135-.6.958-1.136 1.425-.95l.582.32c.524.288.71.961.463 1.511a20.97 20.97 0 0 0-.985 2.783m0 9.18v-9.18" />
                                </svg>
                            @endif
                        </div>
                        <h3 class="text-lg font-bold text-zinc-900">{{ $card['title'] }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-zinc-600">{{ $card['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-zinc-900 sm:text-4xl">Stay informed with the latest news</h2>
                <p class="mt-3 text-zinc-500">Read the latest updates and stories from the Alliance across Lagos.</p>
            </div>

            @if ($latestPosts->isNotEmpty())
                <div
                    class="relative mt-12"
                    x-data="{
                        canPrev: false,
                        canNext: true,
                        sync() {
                            const el = this.$refs.track;
                            if (! el) return;
                            this.canPrev = el.scrollLeft > 4;
                            this.canNext = el.scrollLeft + el.clientWidth < el.scrollWidth - 4;
                        },
                        step() {
                            const el = this.$refs.track;
                            if (! el) return 0;
                            const card = el.querySelector('[data-news-slide]');
                            const gap = 32;
                            return card ? card.getBoundingClientRect().width + gap : el.clientWidth * 0.8;
                        },
                        prev() {
                            this.$refs.track?.scrollBy({ left: -this.step(), behavior: 'smooth' });
                        },
                        next() {
                            this.$refs.track?.scrollBy({ left: this.step(), behavior: 'smooth' });
                        },
                    }"
                    x-init="
                        sync();
                        $refs.track?.addEventListener('scroll', () => sync(), { passive: true });
                        window.addEventListener('resize', () => sync());
                    "
                >
                    <div class="mb-6 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            class="inline-flex size-11 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-900 shadow-sm transition hover:border-lga-green hover:text-lga-green disabled:cursor-not-allowed disabled:opacity-40"
                            @click="prev()"
                            :disabled="! canPrev"
                            aria-label="{{ __('Previous news') }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="size-5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            class="inline-flex size-11 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-900 shadow-sm transition hover:border-lga-green hover:text-lga-green disabled:cursor-not-allowed disabled:opacity-40"
                            @click="next()"
                            :disabled="! canNext"
                            aria-label="{{ __('Next news') }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="size-5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                    </div>

                    <div
                        x-ref="track"
                        class="news-carousel mt-0 pb-2"
                    >
                        @foreach ($latestPosts as $post)
                            <div data-news-slide>
                                <x-news-card :post="$post" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <p class="mt-12 text-center text-zinc-500">{{ __('News coming soon — check back shortly.') }}</p>
            @endif

            <div class="mt-10 text-center">
                <a href="{{ route('posts.index') }}" class="btn-lga-secondary" wire:navigate>{{ __('View all news') }}</a>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
        <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
            <div>
                <h2 class="text-3xl font-bold text-lga-navy">Take the message to your hood.</h2>
                <p class="mt-4 text-zinc-700">
                    Agents are the heartbeat of the Alliance: everyday Lagosians who bring their neighbours,
                    friends and family into one voice.
                </p>
                <ul class="mt-6 space-y-3 text-zinc-700">
                    <li>Carry the message into your own ward.</li>
                    <li>Sign people up and help their voice count.</li>
                    <li>See your impact grow, street by street.</li>
                    <li>Belong to something bigger than yourself.</li>
                </ul>
                <a href="{{ route('become-agent') }}" class="btn-lga-primary mt-8" wire:navigate>Become an Agent</a>
            </div>
            <div class="rounded-3xl bg-lga-navy p-8 text-white">
                <h3 class="text-2xl font-bold">Join in under 2 minutes.</h3>
                <ol class="mt-6 space-y-4">
                    <li><strong>1. Sign up</strong> — Your name and your phone. That's it.</li>
                    <li><strong>2. Verify</strong> — Confirm with a quick code when required — so every voice is real.</li>
                    <li><strong>3. Start moving</strong> — You're in. Add your voice, and bring others with you.</li>
                </ol>
                <a href="{{ route('join') }}" class="btn-lga-primary mt-8" wire:navigate>Get started</a>
            </div>
        </div>
    </section>

    <section class="bg-zinc-50">
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6">
            <h2 class="text-3xl font-bold text-lga-navy">Questions? We've got answers.</h2>
            <div class="mt-8 space-y-4" x-data="{ open: null }">
                @foreach ([
                    ['How do I join the Alliance?', 'Tap Join the Alliance, enter your details with a valid agent referral code, and verify if required. You\'re in — it takes under two minutes.'],
                    ['Does it cost anything?', 'No. Joining is completely free. All it costs is your voice.'],
                    ['Do I have to belong to a political party?', 'No. The Alliance is about Lagos, not any party. Everyone who loves Lagos is welcome, full stop.'],
                    ['What is an Agent?', 'An Agent is a voice-carrier — someone who takes the message into their own community and brings neighbours, friends and family into the movement, ward by ward.'],
                    ['How do I become an Agent?', 'Tap Become an Agent, choose your Local Government and Ward, enter a coordinator referral code, verify your email OTP, set up 2FA, and you\'re ready.'],
                    ['Where does the Alliance operate?', 'Everywhere in Lagos — all 20 Local Governments and every ward. If you\'re in Lagos, we\'re near you.'],
                    ['Is my information safe?', 'Yes. We verify by phone and protect your details. Your information is only ever used to strengthen our collective voice — never sold, never misused.'],
                ] as $index => [$question, $answer])
                    <div class="rounded-xl border border-zinc-200 bg-white">
                        <button type="button" class="flex w-full items-center justify-between px-5 py-4 text-left font-medium" @click="open = open === {{ $index }} ? null : {{ $index }}">
                            <span>{{ $question }}</span>
                            <span x-text="open === {{ $index }} ? '−' : '+'"></span>
                        </button>
                        <div class="px-5 pb-4 text-sm text-zinc-600" x-show="open === {{ $index }}" x-cloak>
                            {{ $answer }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-black py-16 text-center text-white">
        <div class="mx-auto max-w-3xl px-4 sm:px-6">
            <h2 class="text-3xl font-bold">One Lagos. Many voices. Yours matters.</h2>
            <p class="mt-4 text-zinc-300">Join a movement of Lagosians building a stronger Lagos — one voice, one street, one ward at a time.</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ route('join') }}" class="btn-lga-primary" wire:navigate>Join the Alliance</a>
                <a href="{{ route('become-agent') }}" class="btn-lga-secondary-light" wire:navigate>Become an Agent</a>
            </div>
        </div>
    </section>
</x-layouts::public>
