@props(['wireModel' => 'turnstileToken', 'action' => null])

@php
    $siteKey = config('services.turnstile.site_key');
@endphp

@if (filled($siteKey))
    <div
        wire:ignore
        x-data="{ widgetId: null }"
        x-init="
            window.turnstileReady = window.turnstileReady || new Promise((resolve) => {
                if (window.turnstile) { resolve(); return; }
                window.onTurnstileLoad = () => resolve();
            });
            window.turnstileReady.then(() => {
                widgetId = turnstile.render($refs.widget, {
                    sitekey: @js($siteKey),
                    action: @js($action),
                    callback: (token) => $wire.$set(@js($wireModel), token, false),
                    'expired-callback': () => $wire.$set(@js($wireModel), '', false),
                    'error-callback': () => $wire.$set(@js($wireModel), '', false),
                });
            });
        "
        x-on:turnstile-reset.window="if (widgetId !== null) { turnstile.reset(widgetId); }"
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
