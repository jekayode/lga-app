<?php

use App\Enums\Role;
use App\Models\LocalGovernment;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    /** @var list<array{name: string, email: string, phone: string, local_government: string}> $roster */
    $roster = require database_path('seeders/data/lga_coordinators.php');

    collect($roster)
        ->pluck('local_government')
        ->unique()
        ->values()
        ->each(function (string $name, int $index): void {
            LocalGovernment::factory()->create([
                'name' => $name,
                'code' => sprintf('T%02d', $index + 1),
                'state' => 'Lagos',
            ]);
        });
});

test('seed lga coordinators creates accounts with role and lga scoping', function () {
    $this->artisan('membership:seed-lga-coordinators')
        ->assertSuccessful();

    expect(User::query()->where('role', Role::LgaCoordinator)->count())->toBe(20);

    $shomolu = LocalGovernment::query()->where('name', 'Shomolu')->firstOrFail();
    $coordinator = User::query()->where('email', 'tundebalogun2123@yahoo.com')->firstOrFail();

    expect($coordinator)
        ->name->toBe('Balogun Babatunde Nofiu')
        ->role->toBe(Role::LgaCoordinator)
        ->local_government_id->toBe($shomolu->id)
        ->phone->toBe(User::normalizePhone('08083219152'))
        ->is_active->toBeTrue()
        ->must_setup_two_factor->toBeTrue()
        ->email_verified_at->not->toBeNull()
        ->otp_verified_at->not->toBeNull()
        ->referral_code->not->toBeNull();
});

test('seed lga coordinators is idempotent when run twice', function () {
    $this->artisan('membership:seed-lga-coordinators')->assertSuccessful();
    $this->artisan('membership:seed-lga-coordinators')->assertSuccessful();

    expect(User::query()->where('role', Role::LgaCoordinator)->count())->toBe(20);
});

test('seed lga coordinators dry run does not write users', function () {
    $this->artisan('membership:seed-lga-coordinators', ['--dry-run' => true])
        ->assertSuccessful();

    expect(User::query()->count())->toBe(0);
});

test('seed lga coordinators sends password reset notifications', function () {
    Notification::fake();

    $this->artisan('membership:seed-lga-coordinators', ['--send-reset' => true])
        ->assertSuccessful();

    $users = User::query()->where('role', Role::LgaCoordinator)->get();

    expect($users)->toHaveCount(20);

    foreach ($users as $user) {
        Notification::assertSentTo($user, ResetPassword::class);
    }
});

test('seed lga coordinators allows two coordinators for the same lga', function () {
    $this->artisan('membership:seed-lga-coordinators')->assertSuccessful();

    $lagosIsland = LocalGovernment::query()->where('name', 'Lagos Island')->firstOrFail();

    $islandCoordinators = User::query()
        ->where('role', Role::LgaCoordinator)
        ->where('local_government_id', $lagosIsland->id)
        ->get();

    expect($islandCoordinators)->toHaveCount(2)
        ->and($islandCoordinators->pluck('email')->all())
        ->toEqualCanonicalizing([
            'huzboi14@yahoo.com',
            'aduragbemiju49@gmail.com',
        ]);
});
