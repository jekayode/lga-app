<?php

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Support\Facades\Hash;

test('admin seeder never overwrites an existing admin', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@lagosgrassrootsalliance.com',
        'password' => 'a-real-production-password',
    ]);

    $this->seed(AdminUserSeeder::class);

    expect(Hash::check('a-real-production-password', $admin->fresh()->password))->toBeTrue();
});

test('admin seeder outside local uses a random password and requires two factor enrolment', function () {
    app()->detectEnvironment(fn () => 'production');

    app(AdminUserSeeder::class)->run();

    $admin = User::query()->where('email', 'admin@lagosgrassrootsalliance.com')->firstOrFail();

    expect($admin->role)->toBe(Role::Admin)
        ->and(Hash::check('password', $admin->password))->toBeFalse()
        ->and($admin->two_factor_confirmed_at)->toBeNull()
        ->and($admin->hasCompletedMandatoryTwoFactor())->toBeFalse();
});
