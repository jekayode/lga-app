<?php

use App\Enums\Role;
use App\Livewire\Auth\VerifyOtp;
use App\Models\OtpChannelSetting;
use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

beforeEach(function () {
    OtpChannelSetting::query()->updateOrCreate(
        ['role' => Role::CommunityMember->value],
        ['sms_enabled' => false, 'email_enabled' => true, 'whatsapp_enabled' => false]
    );

    $this->user = User::factory()->create([
        'role' => Role::CommunityMember,
        'otp_verified_at' => null,
    ]);

    OtpVerification::query()->create([
        'user_id' => $this->user->id,
        'channel' => 'email',
        'destination' => $this->user->email,
        'code_hash' => Hash::make('123456'),
        'expires_at' => now()->addMinutes(10),
    ]);

    $this->actingAs($this->user);
});

test('otp resends are rate limited per user', function () {
    Mail::fake();

    $component = Livewire::test(VerifyOtp::class);

    foreach (range(1, VerifyOtp::MAX_RESENDS) as $attempt) {
        $component->call('resend')->assertHasNoErrors();
    }

    $component->call('resend')->assertHasErrors('code');

    Mail::assertSentCount(VerifyOtp::MAX_RESENDS);
});

test('otp verification is blocked once the attempt limit is reached', function () {
    foreach (range(1, VerifyOtp::MAX_VERIFY_ATTEMPTS) as $attempt) {
        RateLimiter::hit('otp-verify:'.$this->user->id, VerifyOtp::THROTTLE_DECAY_SECONDS);
    }

    Livewire::test(VerifyOtp::class)
        ->set('code', '123456')
        ->call('verify')
        ->assertHasErrors('code')
        ->assertNoRedirect();

    expect($this->user->fresh()->otp_verified_at)->toBeNull();
});

test('otp channel cannot be changed from the client', function () {
    Livewire::test(VerifyOtp::class)->set('channel', 'sms');
})->throws(CannotUpdateLockedPropertyException::class);

test('cached otp channel settings refresh when updated', function () {
    expect(OtpChannelSetting::forRole(Role::CommunityMember)->enabledChannels())->toBe(['email']);

    OtpChannelSetting::query()
        ->where('role', Role::CommunityMember->value)
        ->first()
        ->update(['email_enabled' => false, 'sms_enabled' => true]);

    expect(OtpChannelSetting::forRole(Role::CommunityMember)->enabledChannels())->toBe(['sms']);
});
