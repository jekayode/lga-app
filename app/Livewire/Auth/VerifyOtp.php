<?php

namespace App\Livewire\Auth;

use App\Enums\OtpChannel;
use App\Services\Otp\OtpBroker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Verify OTP')]
class VerifyOtp extends Component
{
    public const int MAX_RESENDS = 3;

    public const int MAX_VERIFY_ATTEMPTS = 10;

    public const int THROTTLE_DECAY_SECONDS = 600;

    public string $code = '';

    #[Locked]
    public string $channel = 'email';

    public function mount(OtpBroker $otpBroker): void
    {
        $user = Auth::user();

        abort_unless($user, 403);

        if ($user->hasCompletedRequiredOtp()) {
            if ($user->role->requiresMandatoryEmailOtpAndTwoFactor() && ! $user->hasCompletedMandatoryTwoFactor()) {
                $this->redirect(route('two-factor.setup'), navigate: true);

                return;
            }

            $this->redirect(route('dashboard'), navigate: true);

            return;
        }

        $channels = $otpBroker->enabledChannelsFor($user->role);
        $this->channel = $channels[0] ?? 'email';

        $hasPending = $user->otpVerifications()
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->exists();

        if (! $hasPending && $channels !== []) {
            $otpBroker->issue($user, OtpChannel::from($this->channel));
        }
    }

    public function resend(OtpBroker $otpBroker): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $sent = RateLimiter::attempt(
            'otp-resend:'.$user->id,
            self::MAX_RESENDS,
            fn () => $otpBroker->issue($user, OtpChannel::from($this->channel)),
            self::THROTTLE_DECAY_SECONDS,
        );

        if ($sent === false) {
            $minutes = (int) ceil(RateLimiter::availableIn('otp-resend:'.$user->id) / 60);
            $this->addError('code', "Too many codes requested. Try again in {$minutes} minute(s).");

            return;
        }

        session()->flash('status', 'A new verification code has been sent.');
    }

    public function verify(OtpBroker $otpBroker): mixed
    {
        $this->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();
        abort_unless($user, 403);

        $throttleKey = 'otp-verify:'.$user->id;

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_VERIFY_ATTEMPTS)) {
            $minutes = (int) ceil(RateLimiter::availableIn($throttleKey) / 60);
            $this->addError('code', "Too many attempts. Try again in {$minutes} minute(s).");

            return null;
        }

        RateLimiter::hit($throttleKey, self::THROTTLE_DECAY_SECONDS);

        if (! $otpBroker->verify($user, $this->code, OtpChannel::from($this->channel))) {
            $this->addError('code', 'The verification code is invalid or has expired.');

            return null;
        }

        RateLimiter::clear($throttleKey);

        $user->refresh();

        if ($user->role->requiresMandatoryEmailOtpAndTwoFactor() && ! $user->hasCompletedMandatoryTwoFactor()) {
            return $this->redirect(route('two-factor.setup'), navigate: true);
        }

        return $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.verify-otp');
    }
}
