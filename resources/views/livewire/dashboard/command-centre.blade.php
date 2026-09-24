<div class="flex flex-col gap-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Command Centre') }}</flux:heading>
            <flux:text>{{ __('Statewide membership operations across Lagos.') }}</flux:text>
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:button :href="route('dashboard')" wire:navigate>{{ __('Back to dashboard') }}</flux:button>
            <flux:button :href="route('agents.applications')" wire:navigate>{{ __('Agent applications') }}</flux:button>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <flux:card>
            <flux:heading size="sm">{{ __('Members') }}</flux:heading>
            <p class="mt-2 text-3xl font-bold">{{ number_format($metrics['total_members']) }}</p>
            <flux:text class="mt-1">{{ __(':count in the last 7 days', ['count' => $metrics['members_last_7_days']]) }}</flux:text>
        </flux:card>
        <flux:card>
            <flux:heading size="sm">{{ __('Agents') }}</flux:heading>
            <p class="mt-2 text-3xl font-bold">{{ number_format($metrics['total_agents']) }}</p>
            <flux:text class="mt-1">{{ __(':count in the last 7 days', ['count' => $metrics['agents_last_7_days']]) }}</flux:text>
        </flux:card>
        <flux:card>
            <flux:heading size="sm">{{ __('Ward coverage') }}</flux:heading>
            <p class="mt-2 text-3xl font-bold">{{ $metrics['ward_coverage_percent'] }}%</p>
            <flux:text class="mt-1">{{ __(':with of :total wards have members', ['with' => $metrics['wards_with_members'], 'total' => $metrics['total_wards']]) }}</flux:text>
        </flux:card>
        <flux:card>
            <flux:heading size="sm">{{ __('Pending applications') }}</flux:heading>
            <p class="mt-2 text-3xl font-bold">{{ number_format($metrics['pending_applications_count']) }}</p>
            <flux:text class="mt-1">{{ __(':count members with disability noted', ['count' => $metrics['disability_count']]) }}</flux:text>
        </flux:card>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <flux:card>
            <flux:heading size="sm">{{ __('Ward coordinators') }}</flux:heading>
            <p class="mt-2 text-2xl font-bold">{{ number_format($metrics['total_ward_coordinators']) }}</p>
        </flux:card>
        <flux:card>
            <flux:heading size="sm">{{ __('LGA coordinators') }}</flux:heading>
            <p class="mt-2 text-2xl font-bold">{{ number_format($metrics['total_lga_coordinators']) }}</p>
        </flux:card>
    </div>

    <flux:card>
        <flux:heading size="lg" class="mb-4">{{ __('Local Government Area breakdown') }}</flux:heading>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b text-left">
                        <th class="py-2 pe-4">{{ __('LGA') }}</th>
                        <th class="py-2 pe-4">{{ __('Members') }}</th>
                        <th class="py-2">{{ __('Agents') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($metrics['lgas'] as $lga)
                        <tr class="border-b">
                            <td class="py-2 pe-4 font-medium">{{ $lga->name }}</td>
                            <td class="py-2 pe-4">{{ number_format($lga->members_count) }}</td>
                            <td class="py-2">{{ number_format($lga->agents_count) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </flux:card>

    <div class="grid gap-4 lg:grid-cols-2">
        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Top wards') }}</flux:heading>
            <ol class="space-y-2">
                @forelse ($metrics['top_wards'] as $index => $ward)
                    <li class="flex justify-between gap-3 border-b py-2 text-sm">
                        <span>{{ $index + 1 }}. {{ $ward->name }} <span class="text-zinc-500">({{ $ward->localGovernment?->name }})</span></span>
                        <strong>{{ number_format($ward->members_count) }}</strong>
                    </li>
                @empty
                    <li class="text-sm text-zinc-500">{{ __('No ward data yet.') }}</li>
                @endforelse
            </ol>
        </flux:card>

        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Top agents') }}</flux:heading>
            <ol class="space-y-2">
                @forelse ($metrics['top_agents'] as $index => $agent)
                    <li class="flex justify-between gap-3 border-b py-2 text-sm">
                        <span>{{ $index + 1 }}. {{ $agent->name }} <span class="text-zinc-500">({{ $agent->ward?->name }})</span></span>
                        <strong>{{ number_format($agent->members_count) }}</strong>
                    </li>
                @empty
                    <li class="text-sm text-zinc-500">{{ __('No agent activations yet.') }}</li>
                @endforelse
            </ol>
        </flux:card>
    </div>

    <flux:card>
        <flux:heading size="lg" class="mb-4">{{ __('Recent agent applications') }}</flux:heading>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b text-left">
                        <th class="py-2 pe-4">{{ __('Applicant') }}</th>
                        <th class="py-2 pe-4">{{ __('LGA') }}</th>
                        <th class="py-2 pe-4">{{ __('Ward') }}</th>
                        <th class="py-2">{{ __('Submitted') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($metrics['pending_applications'] as $application)
                        <tr class="border-b">
                            <td class="py-2 pe-4">{{ $application->user?->name }}</td>
                            <td class="py-2 pe-4">{{ $application->user?->localGovernment?->name }}</td>
                            <td class="py-2 pe-4">{{ $application->user?->ward?->name }}</td>
                            <td class="py-2">{{ $application->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 text-zinc-500">{{ __('No pending applications.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </flux:card>
</div>
