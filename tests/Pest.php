<?php

use App\Models\LocalGovernment;
use App\Models\PollingUnit;
use App\Models\Profession;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/**
 * @return array{profession: Profession, lga: LocalGovernment, ward: Ward, pollingUnit: PollingUnit}
 */
function membershipLocation(): array
{
    $profession = Profession::factory()->create();
    $lga = LocalGovernment::factory()->create();
    $ward = Ward::factory()->create(['local_government_id' => $lga->id]);
    $pollingUnit = PollingUnit::factory()->create(['ward_id' => $ward->id]);

    return compact('profession', 'lga', 'ward', 'pollingUnit');
}

function makeAgent(array $overrides = []): User
{
    $location = membershipLocation();

    return User::factory()->agent()->withTwoFactor()->create([
        'profession_id' => $location['profession']->id,
        'local_government_id' => $location['lga']->id,
        'ward_id' => $location['ward']->id,
        'polling_unit_id' => $location['pollingUnit']->id,
        'email_verified_at' => now(),
        'otp_verified_at' => now(),
        'must_setup_two_factor' => false,
        ...$overrides,
    ]);
}

function makeWardCoordinator(array $overrides = []): User
{
    $location = membershipLocation();

    return User::factory()->wardCoordinator()->withTwoFactor()->create([
        'profession_id' => $location['profession']->id,
        'local_government_id' => $location['lga']->id,
        'ward_id' => $location['ward']->id,
        'polling_unit_id' => $location['pollingUnit']->id,
        'email_verified_at' => now(),
        'otp_verified_at' => now(),
        'must_setup_two_factor' => false,
        ...$overrides,
    ]);
}

function makeStateManager(array $overrides = []): User
{
    return User::factory()->stateManager()->withTwoFactor()->create([
        'email_verified_at' => now(),
        'otp_verified_at' => now(),
        'must_setup_two_factor' => false,
        ...$overrides,
    ]);
}

function registrationPayload(User $referrer, array $overrides = []): array
{
    $location = membershipLocation();

    return [
        'name' => 'Test Member',
        'email' => 'member'.uniqid().'@example.com',
        'phone' => '080'.fake()->unique()->numerify('########'),
        'password' => 'password',
        'password_confirmation' => 'password',
        'referral_code' => $referrer->referral_code,
        'profession_id' => $location['profession']->id,
        'has_disability' => false,
        'disability_notes' => null,
        'local_government_id' => $location['lga']->id,
        'ward_id' => $location['ward']->id,
        'polling_unit_id' => $location['pollingUnit']->id,
        'skip_turnstile' => true,
        ...$overrides,
    ];
}
