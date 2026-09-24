<?php

namespace App\Actions;

use App\Enums\AgentApplicationStatus;
use App\Enums\Role;
use App\Models\AgentApplication;
use App\Models\User;
use App\Services\ReferralCodeGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApproveAgentApplication
{
    public function __construct(private ReferralCodeGenerator $codes) {}

    public function approve(AgentApplication $application, User $reviewer): User
    {
        if ($application->status !== AgentApplicationStatus::Pending) {
            throw ValidationException::withMessages([
                'application' => 'This application has already been reviewed.',
            ]);
        }

        return DB::transaction(function () use ($application, $reviewer) {
            $user = $application->user;

            $user->forceFill([
                'role' => Role::Agent,
                'referral_code' => $user->referral_code ?: $this->codes->generate(),
                'must_setup_two_factor' => true,
                'otp_verified_at' => null,
                'email_verified_at' => null,
                'two_factor_confirmed_at' => null,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
            ])->save();

            $application->update([
                'status' => AgentApplicationStatus::Approved,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            return $user->refresh();
        });
    }
}
