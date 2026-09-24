<?php

namespace App\Models;

use App\Enums\Role;
use App\Services\ReferralCodeGenerator;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property Role $role
 * @property string|null $referral_code
 * @property int|null $referred_by_id
 * @property int|null $profession_id
 * @property bool $has_disability
 * @property string|null $disability_notes
 * @property int|null $local_government_id
 * @property int|null $ward_id
 * @property int|null $polling_unit_id
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $phone_verified_at
 * @property Carbon|null $otp_verified_at
 * @property bool $must_setup_two_factor
 * @property bool $is_active
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'name',
    'email',
    'phone',
    'password',
    'role',
    'referral_code',
    'referred_by_id',
    'profession_id',
    'has_disability',
    'disability_notes',
    'local_government_id',
    'ward_id',
    'polling_unit_id',
    'email_verified_at',
    'phone_verified_at',
    'otp_verified_at',
    'must_setup_two_factor',
    'is_active',
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === Role::Admin && $this->is_active;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'otp_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'has_disability' => 'boolean',
            'must_setup_two_factor' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if ($user->role === null) {
                $user->role = Role::CommunityMember;
            }

            if (blank($user->referral_code) && $user->shouldHaveReferralCode()) {
                $user->referral_code = app(ReferralCodeGenerator::class)->generate();
            }

            if ($user->role->requiresMandatoryEmailOtpAndTwoFactor() && $user->two_factor_confirmed_at === null) {
                $user->must_setup_two_factor = true;
            }
        });
    }

    public function shouldHaveReferralCode(): bool
    {
        return $this->role->canIssueMemberReferralCodes()
            || $this->role->canIssueAgentReferralCodes();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by_id');
    }

    /**
     * @return HasMany<User, $this>
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by_id');
    }

    /**
     * @return BelongsTo<Profession, $this>
     */
    public function profession(): BelongsTo
    {
        return $this->belongsTo(Profession::class);
    }

    /**
     * @return BelongsTo<LocalGovernment, $this>
     */
    public function localGovernment(): BelongsTo
    {
        return $this->belongsTo(LocalGovernment::class);
    }

    /**
     * @return BelongsTo<Ward, $this>
     */
    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    /**
     * @return BelongsTo<PollingUnit, $this>
     */
    public function pollingUnit(): BelongsTo
    {
        return $this->belongsTo(PollingUnit::class);
    }

    /**
     * @return HasMany<AgentApplication, $this>
     */
    public function agentApplications(): HasMany
    {
        return $this->hasMany(AgentApplication::class);
    }

    /**
     * @return HasMany<OtpVerification, $this>
     */
    public function otpVerifications(): HasMany
    {
        return $this->hasMany(OtpVerification::class);
    }

    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    public function hasCompletedMandatoryTwoFactor(): bool
    {
        if (! $this->role->requiresMandatoryEmailOtpAndTwoFactor()) {
            return true;
        }

        return $this->two_factor_confirmed_at !== null && ! $this->must_setup_two_factor;
    }

    public function hasCompletedRequiredOtp(): bool
    {
        if ($this->role->requiresMandatoryEmailOtpAndTwoFactor()) {
            return $this->email_verified_at !== null && $this->otp_verified_at !== null;
        }

        $settings = OtpChannelSetting::forRole($this->role);

        if (! $settings->requiresOtp()) {
            return true;
        }

        return $this->otp_verified_at !== null;
    }

    public function canAccessDashboard(): bool
    {
        return $this->is_active
            && $this->hasCompletedRequiredOtp()
            && $this->hasCompletedMandatoryTwoFactor();
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function isStateManager(): bool
    {
        return $this->role === Role::StateManager;
    }

    public function isLgaCoordinator(): bool
    {
        return $this->role === Role::LgaCoordinator;
    }

    public function isWardCoordinator(): bool
    {
        return $this->role === Role::WardCoordinator;
    }

    public function isAgent(): bool
    {
        return $this->role === Role::Agent;
    }

    public function isCommunityMember(): bool
    {
        return $this->role === Role::CommunityMember;
    }

    #[Scope]
    protected function verifiedMembers(Builder $query): Builder
    {
        return $query->where('role', Role::CommunityMember)
            ->where('is_active', true)
            ->where(function (Builder $builder): void {
                $builder->whereNotNull('otp_verified_at')
                    ->orWhere(function (Builder $inner): void {
                        $inner->whereNull('otp_verified_at')
                            ->whereDoesntHave('otpVerifications');
                    });
            });
    }

    #[Scope]
    protected function agents(Builder $query): Builder
    {
        return $query->where('role', Role::Agent)->where('is_active', true);
    }

    #[Scope]
    protected function inWard(Builder $query, int $wardId): Builder
    {
        return $query->where('ward_id', $wardId);
    }

    #[Scope]
    protected function inLga(Builder $query, int $localGovernmentId): Builder
    {
        return $query->where('local_government_id', $localGovernmentId);
    }

    public static function findForLogin(string $login): ?self
    {
        $login = trim($login);

        return static::query()
            ->where(function (Builder $query) use ($login): void {
                $query->where('email', $login)
                    ->orWhere('phone', $login)
                    ->orWhere('phone', static::normalizePhone($login));
            })
            ->first();
    }

    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '234') && strlen($digits) >= 13) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            return '+234'.substr($digits, 1);
        }

        if (strlen($digits) === 10) {
            return '+234'.$digits;
        }

        return str_starts_with($phone, '+') ? $phone : '+'.$digits;
    }
}
