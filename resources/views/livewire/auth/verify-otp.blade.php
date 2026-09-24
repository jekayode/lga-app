<div class="flex flex-col gap-6">
    <x-auth-header
        :title="__('Verify your account')"
        :description="__('Enter the 6-digit code we sent you.')"
    />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="verify" class="flex flex-col gap-4">
        <flux:input wire:model="code" :label="__('Verification code')" type="text" inputmode="numeric" maxlength="6" required autofocus />

        <flux:button type="submit" variant="primary" class="w-full">
            {{ __('Verify') }}
        </flux:button>
    </form>

    <div class="text-center text-sm">
        <button type="button" wire:click="resend" class="text-accent underline">
            {{ __('Resend code') }}
        </button>
    </div>
</div>
