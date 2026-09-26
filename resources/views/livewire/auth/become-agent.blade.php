<div class="flex flex-col gap-6">
    <x-auth-header
        :title="__('Coordinators Corner')"
        :description="__('Take the Alliance into your ward. A coordinator referral code is required.')"
    />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-4" novalidate>
        @if ($errors->any())
            <div class="rounded-lg border border-red-500/40 bg-red-500/10 p-3 text-sm text-red-600 dark:text-red-400" role="alert">
                <p class="font-medium">{{ __('Please fix the following:') }}</p>
                <ul class="mt-2 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <flux:input wire:model="name" :label="__('Full name')" type="text" required autofocus autocomplete="name" />
            <flux:input wire:model="email" :label="__('Email address')" type="email" required autocomplete="email" />
            <flux:input wire:model="phone" :label="__('Phone number')" type="tel" required autocomplete="tel" placeholder="08012345678" />
            <flux:input wire:model="referral_code" :label="__('Coordinator referral code')" type="text" required class="uppercase" />

            <flux:select wire:model.live="profession_id" :label="__('Profession')" required>
                <option value="">{{ __('Select profession') }}</option>
                @foreach ($this->professions as $profession)
                    <option value="{{ $profession->id }}">{{ $profession->name }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="local_government_id" :label="__('Local Government Area')" required>
                <option value="">{{ __('Select LGA') }}</option>
                @foreach ($this->localGovernments as $lga)
                    <option value="{{ $lga->id }}">{{ $lga->name }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="ward_id" :label="__('Ward (Registration Area)')" required :disabled="! $local_government_id">
                <option value="">{{ __('Select ward') }}</option>
                @foreach ($this->wards as $ward)
                    <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model="polling_unit_id" :label="__('Polling Unit')" required :disabled="! $ward_id">
                <option value="">{{ __('Select polling unit') }}</option>
                @foreach ($this->pollingUnits as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                @endforeach
            </flux:select>
        </div>

        <flux:checkbox wire:model.live="has_disability" :label="__('I have a disability')" />

        @if ($has_disability)
            <flux:input wire:model="disability_notes" :label="__('Disability details (optional)')" type="text" />
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            <flux:input wire:model="password" :label="__('Password')" type="password" required autocomplete="new-password" viewable />
            <flux:input wire:model="password_confirmation" :label="__('Confirm password')" type="password" required autocomplete="new-password" viewable />
        </div>

        <p class="text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Agents must verify an email OTP and set up two-factor authentication before accessing the dashboard.') }}
        </p>

        <x-turnstile wire:model="turnstileToken" />

        <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
            {{ __('Join Coordinators Corner') }}
        </flux:button>
    </form>

    <div class="space-x-1 text-center text-sm text-zinc-600 dark:text-zinc-400">
        <span>{{ __('Already have an account?') }}</span>
        <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
    </div>
</div>
