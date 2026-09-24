<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+2348'.fake()->unique()->numerify('#########'),
            'email_verified_at' => now(),
            'otp_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => Role::CommunityMember,
            'has_disability' => false,
            'must_setup_two_factor' => false,
            'is_active' => true,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'otp_verified_at' => null,
        ]);
    }

    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
            'must_setup_two_factor' => false,
        ]);
    }

    public function agent(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::Agent,
            'must_setup_two_factor' => true,
            'email_verified_at' => now(),
            'otp_verified_at' => now(),
        ]);
    }

    public function wardCoordinator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::WardCoordinator,
            'must_setup_two_factor' => true,
            'email_verified_at' => now(),
            'otp_verified_at' => now(),
        ]);
    }

    public function lgaCoordinator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::LgaCoordinator,
            'must_setup_two_factor' => true,
            'email_verified_at' => now(),
            'otp_verified_at' => now(),
        ]);
    }

    public function stateManager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::StateManager,
            'must_setup_two_factor' => true,
            'email_verified_at' => now(),
            'otp_verified_at' => now(),
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::Admin,
            'must_setup_two_factor' => false,
            'email_verified_at' => now(),
            'otp_verified_at' => now(),
            'two_factor_confirmed_at' => now(),
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
        ]);
    }
}
