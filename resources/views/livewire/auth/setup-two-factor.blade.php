<div class="flex flex-col gap-6">
    <x-auth-header
        :title="__('Set up two-factor authentication')"
        :description="__('Scan the QR code with your authenticator app, then enter a code to confirm.')"
    />

    @if ($qrCodeSvg)
        <div class="mx-auto rounded-lg bg-white p-4 shadow">
            {!! $qrCodeSvg !!}
        </div>
        <p class="text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Or enter this key manually:') }}
            <code class="font-mono">{{ $secret }}</code>
        </p>
    @endif

    <form wire:submit="confirm" class="flex flex-col gap-4">
        <flux:input wire:model="code" :label="__('Authentication code')" type="text" inputmode="numeric" required autofocus />

        <flux:button type="submit" variant="primary" class="w-full">
            {{ __('Confirm and continue') }}
        </flux:button>
    </form>
</div>
