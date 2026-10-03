<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Create the platform admin once, without ever overwriting an existing account.
     *
     * Outside local/testing a random password is generated and printed once, and the admin
     * must enrol in two-factor authentication on first sign-in.
     */
    public function run(): void
    {
        $email = 'admin@lagosgrassrootsalliance.com';

        if (User::query()->where('email', $email)->exists()) {
            $this->command?->info("Admin {$email} already exists; leaving it unchanged.");

            return;
        }

        $isLocal = app()->environment('local', 'testing');
        $password = $isLocal ? 'password' : Str::password(24);

        $admin = new User;
        $admin->forceFill([
            'name' => 'Platform Admin',
            'email' => $email,
            'phone' => '+2348000000001',
            'password' => $password,
            'role' => Role::Admin,
            'email_verified_at' => now(),
            'otp_verified_at' => now(),
            'must_setup_two_factor' => ! $isLocal,
            'two_factor_confirmed_at' => $isLocal ? now() : null,
            'is_active' => true,
        ])->save();

        if (! $isLocal) {
            $this->command?->warn("Admin created: {$email}");
            $this->command?->warn("Temporary password (shown once, change it after first login): {$password}");
        }
    }
}
