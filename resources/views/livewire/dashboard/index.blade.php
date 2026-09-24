<div class="flex flex-col gap-6">
    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Dashboard') }}</flux:heading>
            <flux:text>{{ $user->role->label() }} · {{ $user->name }}</flux:text>
        </div>

        <div class="flex flex-wrap gap-2">
            @if ($user->isAgent())
                <flux:button :href="route('agents.onboard')" wire:navigate variant="primary">{{ __('Onboard member') }}</flux:button>
            @endif
            @if ($metrics['can_apply_as_agent'] ?? false)
                <flux:button :href="route('agents.apply')" wire:navigate variant="primary">{{ __('Apply to become an Agent') }}</flux:button>
            @endif
            @if ($user->isWardCoordinator() || $user->isAdmin() || $user->isStateManager())
                <flux:button :href="route('agents.applications')" wire:navigate>{{ __('Agent applications') }}</flux:button>
            @endif
        </div>
    </div>

    @if ($user->isCommunityMember())
        <flux:card class="space-y-2">
            <flux:heading size="lg">{{ __('Welcome to the Alliance') }}</flux:heading>
            <flux:text>{{ __('Your voice is part of One Lagos, Many Voices.') }}</flux:text>
            @if ($metrics['pending_application'] ?? null)
                <flux:badge>{{ __('Application status: :status', ['status' => $metrics['pending_application']->status->label()]) }}</flux:badge>
            @endif
        </flux:card>
    @endif

    @if ($user->isAgent())
        <div class="grid gap-4 md:grid-cols-3">
            <flux:card>
                <flux:heading size="sm">{{ __('Your referral code') }}</flux:heading>
                <p class="mt-2 font-mono text-2xl font-bold">{{ $metrics['referral_code'] }}</p>
            </flux:card>
            <flux:card>
                <flux:heading size="sm">{{ __('Members activated') }}</flux:heading>
                <p class="mt-2 text-2xl font-bold">{{ $metrics['members_activated'] }}</p>
            </flux:card>
        </div>

        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Recent activations') }}</flux:heading>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b text-left">
                            <th class="py-2">{{ __('Name') }}</th>
                            <th class="py-2">{{ __('Phone') }}</th>
                            <th class="py-2">{{ __('Ward') }}</th>
                            <th class="py-2">{{ __('Joined') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($metrics['recent_members'] as $member)
                            <tr class="border-b">
                                <td class="py-2">{{ $member->name }}</td>
                                <td class="py-2">{{ $member->phone }}</td>
                                <td class="py-2">{{ $member->ward?->name }}</td>
                                <td class="py-2">{{ $member->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-zinc-500">{{ __('No members activated yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </flux:card>
    @endif

    @if ($user->isWardCoordinator())
        <div class="grid gap-4 md:grid-cols-3">
            <flux:card>
                <flux:heading size="sm">{{ __('Referral code') }}</flux:heading>
                <p class="mt-2 font-mono text-xl font-bold">{{ $metrics['referral_code'] }}</p>
            </flux:card>
            <flux:card>
                <flux:heading size="sm">{{ __('Members in ward') }}</flux:heading>
                <p class="mt-2 text-2xl font-bold">{{ $metrics['members_count'] }}</p>
            </flux:card>
            <flux:card>
                <flux:heading size="sm">{{ __('Agents') }}</flux:heading>
                <p class="mt-2 text-2xl font-bold">{{ $metrics['agents']->count() }}</p>
            </flux:card>
        </div>

        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Polling units') }}</flux:heading>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b text-left">
                            <th class="py-2">{{ __('Polling unit') }}</th>
                            <th class="py-2">{{ __('Members') }}</th>
                            <th class="py-2">{{ __('Agents') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($metrics['polling_units'] as $unit)
                            <tr class="border-b">
                                <td class="py-2">{{ $unit->name }}</td>
                                <td class="py-2">{{ $unit->members_count }}</td>
                                <td class="py-2">{{ $unit->agents_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </flux:card>
    @endif

    @if ($user->isLgaCoordinator())
        <div class="grid gap-4 md:grid-cols-3">
            <flux:card>
                <flux:heading size="sm">{{ __('Referral code') }}</flux:heading>
                <p class="mt-2 font-mono text-xl font-bold">{{ $metrics['referral_code'] }}</p>
            </flux:card>
            <flux:card>
                <flux:heading size="sm">{{ __('Members in LGA') }}</flux:heading>
                <p class="mt-2 text-2xl font-bold">{{ $metrics['members_count'] }}</p>
            </flux:card>
            <flux:card>
                <flux:heading size="sm">{{ __('Agents') }}</flux:heading>
                <p class="mt-2 text-2xl font-bold">{{ $metrics['agents_count'] }}</p>
            </flux:card>
        </div>

        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Wards & polling units') }}</flux:heading>
            <div class="space-y-4">
                @foreach ($metrics['wards'] as $ward)
                    <div class="rounded-lg border p-4">
                        <div class="flex flex-wrap justify-between gap-2">
                            <flux:heading size="sm">{{ $ward->name }}</flux:heading>
                            <flux:text>{{ $ward->members_count }} members · {{ $ward->agents_count }} agents · {{ $ward->pollingUnits->count() }} PUs</flux:text>
                        </div>
                        <ul class="mt-2 list-inside list-disc text-sm text-zinc-600 dark:text-zinc-400">
                            @foreach ($ward->pollingUnits as $unit)
                                <li>{{ $unit->name }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </flux:card>
    @endif

    @if ($user->isStateManager() || $user->isAdmin())
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="grid flex-1 gap-4 md:grid-cols-2">
                <flux:card>
                    <flux:heading size="sm">{{ __('Total members') }}</flux:heading>
                    <p class="mt-2 text-3xl font-bold">{{ $metrics['total_members'] }}</p>
                </flux:card>
                <flux:card>
                    <flux:heading size="sm">{{ __('Total agents') }}</flux:heading>
                    <p class="mt-2 text-3xl font-bold">{{ $metrics['total_agents'] }}</p>
                </flux:card>
            </div>
            <flux:button :href="route('command-centre')" wire:navigate variant="primary">{{ __('Open Command Centre') }}</flux:button>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ __('Top wards') }}</flux:heading>
                <ol class="space-y-2">
                    @foreach ($metrics['top_wards'] as $index => $ward)
                        <li class="flex justify-between border-b py-2 text-sm">
                            <span>{{ $index + 1 }}. {{ $ward->name }} <span class="text-zinc-500">({{ $ward->localGovernment?->name }})</span></span>
                            <strong>{{ $ward->members_count }}</strong>
                        </li>
                    @endforeach
                </ol>
            </flux:card>
            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ __('Top agents') }}</flux:heading>
                <ol class="space-y-2">
                    @foreach ($metrics['top_agents'] as $index => $agent)
                        <li class="flex justify-between border-b py-2 text-sm">
                            <span>{{ $index + 1 }}. {{ $agent->name }}</span>
                            <strong>{{ $agent->members_count }}</strong>
                        </li>
                    @endforeach
                </ol>
            </flux:card>
        </div>
    @endif
</div>
