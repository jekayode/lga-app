<div class="mx-auto max-w-xl space-y-6">
    <flux:heading size="xl">{{ __('Apply to become an Agent') }}</flux:heading>
    <flux:text>{{ __('Tell us why you want to carry the Alliance into your community.') }}</flux:text>

    <form wire:submit="submit" class="space-y-4">
        <flux:textarea wire:model="motivation" :label="__('Motivation')" rows="6" required />
        <flux:button type="submit" variant="primary">{{ __('Submit application') }}</flux:button>
    </form>
</div>
