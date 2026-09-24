<?php

use App\Enums\Role;
use App\Livewire\Auth\VerifyOtp;
use App\Models\OtpChannelSetting;
use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('users can authenticate with email', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'login' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('users can authenticate with phone number', function () {
    $user = User::factory()->create([
        'phone' => '+2348012345678',
    ]);

    $response = $this->post(route('login.store'), [
        'login' => '08012345678',
        'password' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('community members are blocked when otp is required and incomplete', function () {
    OtpChannelSetting::query()->updateOrCreate(
        ['role' => Role::CommunityMember->value],
        ['sms_enabled' => false, 'email_enabled' => true, 'whatsapp_enabled' => false]
    );

    $user = User::factory()->create([
        'role' => Role::CommunityMember,
        'otp_verified_at' => null,
        'email_verified_at' => null,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('otp.verify'));
});

test('agents are redirected to two factor setup when mandatory 2fa is incomplete', function () {
    $user = User::factory()->agent()->create([
        'email_verified_at' => now(),
        'otp_verified_at' => now(),
        'must_setup_two_factor' => true,
        'two_factor_confirmed_at' => null,
        'two_factor_secret' => null,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('two-factor.setup'));
});

test('community members can verify otp when required', function () {
    OtpChannelSetting::query()->updateOrCreate(
        ['role' => Role::CommunityMember->value],
        ['sms_enabled' => false, 'email_enabled' => true, 'whatsapp_enabled' => false]
    );

    $user = User::factory()->create([
        'role' => Role::CommunityMember,
        'otp_verified_at' => null,
    ]);

    OtpVerification::query()->create([
        'user_id' => $user->id,
        'channel' => 'email',
        'destination' => $user->email,
        'code_hash' => Hash::make('123456'),
        'expires_at' => now()->addMinutes(10),
    ]);

    $this->actingAs($user);

    Livewire::test(VerifyOtp::class)
        ->set('code', '123456')
        ->call('verify')
        ->assertRedirect(route('dashboard'));

    expect($user->fresh()->otp_verified_at)->not->toBeNull();
});

test('password reset accepts phone number', function () {
    Notification::fake();

    $user = User::factory()->create([
        'phone' => '+2348099988776',
    ]);

    $this->post(route('password.email'), [
        'login' => '08099988776',
    ])->assertSessionHasNoErrors();

    Notification::assertSentTo($user, ResetPassword::class);
});
