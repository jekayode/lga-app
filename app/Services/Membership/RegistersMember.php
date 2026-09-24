<?php

namespace App\Services\Membership;

use App\Enums\OtpChannel;
use App\Enums\Role;
use App\Models\User;
use App\Models\Ward;
use App\Rules\Turnstile;
use App\Services\Otp\OtpBroker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RegistersMember
{
    public function __construct(private OtpBroker $otpBroker) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public function registerCommunityMember(array $input, ?User $onboardingAgent = null): User
    {
        $agent = $onboardingAgent ?? $this->resolveReferrer(
            $input['referral_code'] ?? null,
            Role::Agent
        );

        if (! $agent || ! $agent->isAgent() || ! $agent->is_active) {
            throw ValidationException::withMessages([
                'referral_code' => 'A valid agent referral code is required.',
            ]);
        }

        $user = $this->createUser($input, Role::CommunityMember, $agent);

        $this->maybeIssueOtp($user, $input['otp_channel'] ?? null);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function registerAgent(array $input): User
    {
        $referrer = $this->resolveReferrer(
            $input['referral_code'] ?? null,
            null
        );

        if (! $referrer || ! $referrer->role->canIssueAgentReferralCodes() || ! $referrer->is_active) {
            throw ValidationException::withMessages([
                'referral_code' => 'A valid coordinator referral code is required.',
            ]);
        }

        $user = $this->createUser($input, Role::Agent, $referrer);

        $this->otpBroker->issue($user, OtpChannel::Email);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    protected function createUser(array $input, Role $role, User $referrer): User
    {
        if (isset($input['phone'])) {
            $input['phone'] = User::normalizePhone((string) $input['phone']);
        }

        if (isset($input['referral_code'])) {
            $input['referral_code'] = strtoupper(trim((string) $input['referral_code']));
        }

        // Livewire binds Turnstile to turnstileToken; accept either key.
        if (! isset($input['cf-turnstile-response']) && isset($input['turnstileToken'])) {
            $input['cf-turnstile-response'] = $input['turnstileToken'];
        }

        $skipTurnstile = (bool) ($input['skip_turnstile'] ?? false)
            || app()->environment('testing')
            || blank(config('services.turnstile.secret_key'));

        $validated = Validator::make($input, $this->rules($role, $skipTurnstile))->validate();

        $phone = $validated['phone'];
        $ward = Ward::query()->with('localGovernment')->findOrFail($validated['ward_id']);

        if ((int) $ward->local_government_id !== (int) $validated['local_government_id']) {
            throw ValidationException::withMessages([
                'ward_id' => 'The selected ward does not belong to the selected LGA.',
            ]);
        }

        return DB::transaction(function () use ($validated, $role, $referrer, $phone, $ward) {
            return User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $phone,
                'password' => $validated['password'],
                'role' => $role,
                'referred_by_id' => $referrer->id,
                'profession_id' => $validated['profession_id'],
                'has_disability' => (bool) ($validated['has_disability'] ?? false),
                'disability_notes' => $validated['disability_notes'] ?? null,
                'local_government_id' => $ward->local_government_id,
                'ward_id' => $ward->id,
                'polling_unit_id' => $validated['polling_unit_id'],
                'must_setup_two_factor' => $role->requiresMandatoryEmailOtpAndTwoFactor(),
                'is_active' => true,
            ]);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(Role $role, bool $skipTurnstile = false): array
    {
        $channels = $this->otpBroker->enabledChannelsFor($role);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
            'profession_id' => ['required', 'exists:professions,id'],
            'has_disability' => ['sometimes', 'boolean'],
            'disability_notes' => ['nullable', 'string', 'max:255'],
            'local_government_id' => ['required', 'exists:local_governments,id'],
            'ward_id' => ['required', 'exists:wards,id'],
            'polling_unit_id' => ['required', 'exists:polling_units,id'],
            'referral_code' => ['required', 'string', 'max:32'],
            'otp_channel' => [
                Rule::requiredIf(count($channels) > 1 && $role === Role::CommunityMember),
                'nullable',
                Rule::in($channels),
            ],
        ];

        if (! $skipTurnstile) {
            $rules['turnstileToken'] = ['required', new Turnstile];
            $rules['cf-turnstile-response'] = ['nullable'];
        }

        return $rules;
    }

    protected function resolveReferrer(?string $code, ?Role $requiredRole): ?User
    {
        if (blank($code)) {
            return null;
        }

        $query = User::query()
            ->where('referral_code', strtoupper(trim($code)))
            ->where('is_active', true);

        if ($requiredRole) {
            $query->where('role', $requiredRole);
        }

        return $query->first();
    }

    protected function maybeIssueOtp(User $user, ?string $channel): void
    {
        $channels = $this->otpBroker->enabledChannelsFor($user->role);

        if ($channels === []) {
            return;
        }

        $selected = $channel ?? (count($channels) === 1 ? $channels[0] : null);

        if ($selected === null) {
            throw ValidationException::withMessages([
                'otp_channel' => 'Please select an OTP channel.',
            ]);
        }

        $this->otpBroker->issue($user, OtpChannel::from($selected));
    }
}
