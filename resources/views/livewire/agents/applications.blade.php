<div class="space-y-6">
    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <flux:heading size="xl">{{ __('Pending agent applications') }}</flux:heading>

    <div class="space-y-4">
        @forelse ($applications as $application)
            <flux:card class="space-y-3">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <flux:heading size="sm">{{ $application->user->name }}</flux:heading>
                        <flux:text>{{ $application->user->phone }} · {{ $application->user->ward?->name }}</flux:text>
                    </div>
                    <div class="flex gap-2">
                        <flux:button size="sm" variant="primary" wire:click="approve({{ $application->id }})">{{ __('Approve') }}</flux:button>
                        <flux:button size="sm" variant="danger" wire:click="reject({{ $application->id }})">{{ __('Reject') }}</flux:button>
                    </div>
                </div>
                <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $application->motivation }}</p>
            </flux:card>
        @empty
            <flux:text>{{ __('No pending applications.') }}</flux:text>
        @endforelse
    </div>
</div>
