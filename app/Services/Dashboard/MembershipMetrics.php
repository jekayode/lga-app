<?php

namespace App\Services\Dashboard;

use App\Enums\AgentApplicationStatus;
use App\Enums\Role;
use App\Models\AgentApplication;
use App\Models\LocalGovernment;
use App\Models\OtpChannelSetting;
use App\Models\PollingUnit;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Support\Collection;

class MembershipMetrics
{
    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        return match ($user->role) {
            Role::Agent => $this->forAgent($user),
            Role::WardCoordinator => $this->forWardCoordinator($user),
            Role::LgaCoordinator => $this->forLgaCoordinator($user),
            Role::StateManager, Role::Admin => $this->forStatewide($user),
            default => $this->forMember($user),
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function forMember(User $user): array
    {
        return [
            'role' => $user->role,
            'can_apply_as_agent' => $user->isCommunityMember()
                && ! $user->agentApplications()->where('status', AgentApplicationStatus::Pending)->exists(),
            'pending_application' => $user->agentApplications()->latest()->first(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function forAgent(User $user): array
    {
        return [
            'role' => $user->role,
            'referral_code' => $user->referral_code,
            'members_activated' => $this->verifiedMembersQuery()->where('referred_by_id', $user->id)->count(),
            'recent_members' => $this->verifiedMembersQuery()
                ->where('referred_by_id', $user->id)
                ->latest()
                ->limit(10)
                ->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function forWardCoordinator(User $user): array
    {
        abort_unless($user->ward_id, 403);

        return [
            'role' => $user->role,
            'referral_code' => $user->referral_code,
            'polling_units' => PollingUnit::query()
                ->active()
                ->where('ward_id', $user->ward_id)
                ->withCount([
                    'users as members_count' => fn ($q) => $q->where('role', Role::CommunityMember)->where('is_active', true),
                    'users as agents_count' => fn ($q) => $q->where('role', Role::Agent)->where('is_active', true),
                ])
                ->get(),
            'agents' => User::query()->agents()->inWard($user->ward_id)->withCount([
                'referrals as members_count' => fn ($q) => $q->where('role', Role::CommunityMember)->where('is_active', true),
            ])->get(),
            'members_count' => $this->verifiedMembersQuery()->inWard($user->ward_id)->count(),
            'pending_applications' => AgentApplication::query()
                ->pending()
                ->whereHas('user', fn ($q) => $q->where('ward_id', $user->ward_id))
                ->with('user')
                ->latest()
                ->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function forLgaCoordinator(User $user): array
    {
        abort_unless($user->local_government_id, 403);

        return [
            'role' => $user->role,
            'referral_code' => $user->referral_code,
            'wards' => Ward::query()
                ->active()
                ->where('local_government_id', $user->local_government_id)
                ->with(['pollingUnits' => fn ($q) => $q->active()])
                ->withCount([
                    'users as members_count' => fn ($q) => $q->where('role', Role::CommunityMember)->where('is_active', true),
                    'users as agents_count' => fn ($q) => $q->where('role', Role::Agent)->where('is_active', true),
                ])
                ->orderBy('name')
                ->get(),
            'members_count' => $this->verifiedMembersQuery()->inLga($user->local_government_id)->count(),
            'agents_count' => User::query()->agents()->inLga($user->local_government_id)->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function forStatewide(User $user): array
    {
        return [
            'role' => $user->role,
            'referral_code' => $user->referral_code,
            'total_members' => $this->verifiedMembersQuery()->count(),
            'total_agents' => User::query()->agents()->count(),
            'top_wards' => $this->topWards(10),
            'top_agents' => $this->topAgents(10),
            'pending_applications' => AgentApplication::query()->pending()->with('user')->latest()->limit(20)->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forCommandCentre(User $user): array
    {
        abort_unless($user->isStateManager() || $user->isAdmin(), 403);

        $totalWards = Ward::query()->active()->count();
        $wardsWithMembers = Ward::query()
            ->active()
            ->whereHas('users', function ($query): void {
                $query->where('role', Role::CommunityMember)->where('is_active', true);
                $this->applyVerifiedConstraint($query);
            })
            ->count();

        return [
            'total_members' => $this->verifiedMembersQuery()->count(),
            'total_agents' => User::query()->agents()->count(),
            'total_ward_coordinators' => User::query()->where('role', Role::WardCoordinator)->where('is_active', true)->count(),
            'total_lga_coordinators' => User::query()->where('role', Role::LgaCoordinator)->where('is_active', true)->count(),
            'pending_applications_count' => AgentApplication::query()->pending()->count(),
            'members_last_7_days' => $this->verifiedMembersQuery()->where('created_at', '>=', now()->subDays(7))->count(),
            'agents_last_7_days' => User::query()->agents()->where('created_at', '>=', now()->subDays(7))->count(),
            'disability_count' => $this->verifiedMembersQuery()->where('has_disability', true)->count(),
            'total_wards' => $totalWards,
            'wards_with_members' => $wardsWithMembers,
            'ward_coverage_percent' => $totalWards > 0
                ? (int) round(($wardsWithMembers / $totalWards) * 100)
                : 0,
            'lgas' => LocalGovernment::query()
                ->active()
                ->withCount([
                    'users as members_count' => function ($query): void {
                        $query->where('role', Role::CommunityMember)->where('is_active', true);
                        $this->applyVerifiedConstraint($query);
                    },
                    'users as agents_count' => fn ($query) => $query->where('role', Role::Agent)->where('is_active', true),
                ])
                ->orderBy('name')
                ->get(),
            'top_wards' => $this->topWards(10),
            'top_agents' => $this->topAgents(10),
            'pending_applications' => AgentApplication::query()
                ->pending()
                ->with(['user.ward', 'user.localGovernment'])
                ->latest()
                ->limit(15)
                ->get(),
        ];
    }

    /**
     * @return Collection<int, Ward>
     */
    public function topWards(int $limit = 10): Collection
    {
        return Ward::query()
            ->active()
            ->with('localGovernment')
            ->withCount([
                'users as members_count' => function ($query): void {
                    $query->where('role', Role::CommunityMember)->where('is_active', true);
                    $this->applyVerifiedConstraint($query);
                },
            ])
            ->orderByDesc('members_count')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    public function topAgents(int $limit = 10): Collection
    {
        return User::query()
            ->agents()
            ->with(['ward', 'localGovernment'])
            ->withCount([
                'referrals as members_count' => function ($query): void {
                    $query->where('role', Role::CommunityMember)->where('is_active', true);
                    $this->applyVerifiedConstraint($query);
                },
            ])
            ->orderByDesc('members_count')
            ->limit($limit)
            ->get();
    }

    protected function verifiedMembersQuery()
    {
        $query = User::query()
            ->where('role', Role::CommunityMember)
            ->where('is_active', true);

        $this->applyVerifiedConstraint($query);

        return $query;
    }

    protected function applyVerifiedConstraint($query): void
    {
        $settings = OtpChannelSetting::forRole(Role::CommunityMember);

        if ($settings->requiresOtp()) {
            $query->whereNotNull('otp_verified_at');
        }
    }
}
