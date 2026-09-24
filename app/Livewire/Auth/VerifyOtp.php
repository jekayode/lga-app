<?php

namespace App\Livewire\Auth;

use App\Enums\OtpChannel;
use App\Services\Otp\OtpBroker;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Verify OTP')]
class VerifyOtp extends Component
{
    public string $code = '';

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

        $otpBroker->issue($user, OtpChannel::from($this->channel));

        session()->flash('status', 'A new verification code has been sent.');
    }

    public function verify(OtpBroker $otpBroker): mixed
    {
        $this->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();
        abort_unless($user, 403);

        if (! $otpBroker->verify($user, $this->code, OtpChannel::from($this->channel))) {
            $this->addError('code', 'The verification code is invalid or has expired.');

            return null;
        }

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
