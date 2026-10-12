<?php

use App\Mail\CoordinatorReferralCodeMail;
use App\Models\LocalGovernment;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();

    $this->lga = LocalGovernment::factory()->create(['name' => 'Eti-Osa']);
    $this->coordinator = User::factory()->lgaCoordinator()->create([
        'email' => 'coordinator@example.com',
        'local_government_id' => $this->lga->id,
    ]);
});

test('emails each active coordinator their own referral code and sign-up link', function () {
    $inactive = User::factory()->lgaCoordinator()->create(['is_active' => false]);
    $agent = makeAgent();

    $this->artisan('membership:send-coordinator-codes')->assertSuccessful();

    Mail::assertSent(CoordinatorReferralCodeMail::class, 1);
    Mail::assertSent(CoordinatorReferralCodeMail::class, function (CoordinatorReferralCodeMail $mail) {
        $html = $mail->render();

        return $mail->hasTo('coordinator@example.com')
            && str_contains($html, $this->coordinator->referral_code)
            && str_contains($html, route('become-agent', ['ref' => $this->coordinator->referral_code]))
            && str_contains($html, 'Eti-Osa');
    });
    Mail::assertNotSent(CoordinatorReferralCodeMail::class, fn ($mail) => $mail->hasTo($inactive->email) || $mail->hasTo($agent->email));
});

test('dry run sends nothing', function () {
    $this->artisan('membership:send-coordinator-codes', ['--dry-run' => true])
        ->expectsOutputToContain($this->coordinator->referral_code)
        ->assertSuccessful();

    Mail::assertNothingSent();
});

test('can target specific coordinators by email', function () {
    User::factory()->lgaCoordinator()->create(['email' => 'other@example.com']);

    $this->artisan('membership:send-coordinator-codes', ['--email' => ['coordinator@example.com']])->assertSuccessful();

    Mail::assertSent(CoordinatorReferralCodeMail::class, 1);
    Mail::assertSent(CoordinatorReferralCodeMail::class, fn ($mail) => $mail->hasTo('coordinator@example.com'));
});

test('generates a referral code for a coordinator who has none', function () {
    $this->coordinator->forceFill(['referral_code' => null])->save();

    $this->artisan('membership:send-coordinator-codes')->assertSuccessful();

    expect($this->coordinator->fresh()->referral_code)->not->toBeNull();
});
