@props(['wireModel' => 'turnstileToken'])

@php
    $siteKey = config('services.turnstile.site_key');
@endphp

@if (filled($siteKey))
    <div
        wire:ignore
        x-data
        x-init="
            window.turnstileReady = window.turnstileReady || new Promise((resolve) => {
                if (window.turnstile) { resolve(); return; }
                window.onTurnstileLoad = () => resolve();
            });
            window.turnstileReady.then(() => {
                turnstile.render($refs.widget, {
                    sitekey: @js($siteKey),
                    callback: (token) => $wire.set(@js($wireModel), token),
                    'expired-callback': () => $wire.set(@js($wireModel), ''),
                });
            });
        "
    >
        <div x-ref="widget"></div>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&onload=onTurnstileLoad" async defer></script>
    </div>
    @error($wireModel)
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
@elseif (! app()->environment('production'))
    <p class="text-xs text-zinc-500">{{ __('Turnstile is disabled until keys are configured.') }}</p>
@endif
