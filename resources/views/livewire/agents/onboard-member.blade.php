<div class="mx-auto max-w-xl space-y-6">
    <flux:heading size="xl">{{ __('Onboard a community member') }}</flux:heading>
    <flux:text>{{ __('Your referral code will be attached automatically.') }}</flux:text>

    <form wire:submit="save" class="flex flex-col gap-4">
        <flux:input wire:model="name" :label="__('Full name')" required />
        <flux:input wire:model="email" :label="__('Email')" type="email" required />
        <flux:input wire:model="phone" :label="__('Phone')" type="tel" required />
        <flux:select wire:model="profession_id" :label="__('Profession')" required>
            <option value="">{{ __('Select') }}</option>
            @foreach ($this->professions as $profession)
                <option value="{{ $profession->id }}">{{ $profession->name }}</option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="local_government_id" :label="__('LGA')" required>
            <option value="">{{ __('Select') }}</option>
            @foreach ($this->localGovernments as $lga)
                <option value="{{ $lga->id }}">{{ $lga->name }}</option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="ward_id" :label="__('Ward')" required>
            <option value="">{{ __('Select') }}</option>
            @foreach ($this->wards as $ward)
                <option value="{{ $ward->id }}">{{ $ward->name }}</option>
            @endforeach
        </flux:select>
        <flux:select wire:model="polling_unit_id" :label="__('Polling unit')" required>
            <option value="">{{ __('Select') }}</option>
            @foreach ($this->pollingUnits as $unit)
                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
            @endforeach
        </flux:select>
        <flux:checkbox wire:model.live="has_disability" :label="__('Has a disability')" />
        @if ($has_disability)
            <flux:input wire:model="disability_notes" :label="__('Disability notes')" />
        @endif
        <flux:input wire:model="password" :label="__('Temporary password')" type="password" required viewable />
        <flux:input wire:model="password_confirmation" :label="__('Confirm password')" type="password" required viewable />
        <flux:button type="submit" variant="primary">{{ __('Activate member') }}</flux:button>
    </form>
</div>
