<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@lagosgrassrootsalliance.com'],
            [
                'name' => 'Platform Admin',
                'phone' => '+2348000000001',
                'password' => Hash::make('password'),
                'role' => Role::Admin,
                'email_verified_at' => now(),
                'otp_verified_at' => now(),
                'must_setup_two_factor' => false,
                'two_factor_confirmed_at' => now(),
                'is_active' => true,
            ]
        );
    }
}
