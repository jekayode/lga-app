<?php

namespace App\Services\Otp;

use App\Enums\OtpChannel;
use App\Enums\Role;
use App\Mail\OtpCodeMail;
use App\Models\OtpChannelSetting;
use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class OtpBroker
{
    public function enabledChannelsFor(Role $role): array
    {
        $settings = OtpChannelSetting::forRole($role);
        $channels = $settings->enabledChannels();

        if ($role->requiresMandatoryEmailOtpAndTwoFactor() && ! in_array('email', $channels, true)) {
            $channels[] = 'email';
        }

        return $channels;
    }

    public function requiresOtp(Role $role): bool
    {
        return count($this->enabledChannelsFor($role)) > 0;
    }

    public function issue(User $user, OtpChannel $channel): OtpVerification
    {
        $destination = match ($channel) {
            OtpChannel::Email => $user->email,
            OtpChannel::Sms, OtpChannel::WhatsApp => $user->phone,
        };

        if (blank($destination)) {
            throw ValidationException::withMessages([
                'channel' => 'A valid destination is required for '.$channel->label().' OTP.',
            ]);
        }

        $code = (string) random_int(100000, 999999);

        $verification = OtpVerification::query()->create([
            'user_id' => $user->id,
            'channel' => $channel,
            'destination' => $destination,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
        ]);

        $this->dispatch($channel, $destination, $code, $user);

        return $verification;
    }

    public function verify(User $user, string $code, ?OtpChannel $channel = null): bool
    {
        $query = OtpVerification::query()
            ->where('user_id', $user->id)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest();

        if ($channel) {
            $query->where('channel', $channel);
        }

        /** @var OtpVerification|null $verification */
        $verification = $query->first();

        if (! $verification) {
            return false;
        }

        if ($verification->attempts >= 5) {
            throw ValidationException::withMessages([
                'code' => 'Too many invalid OTP attempts. Request a new code.',
            ]);
        }

        $verification->increment('attempts');

        if (! Hash::check($code, $verification->code_hash)) {
            return false;
        }

        $verification->forceFill(['verified_at' => now()])->save();

        $user->forceFill([
            'otp_verified_at' => now(),
            'email_verified_at' => $verification->channel === OtpChannel::Email
                ? ($user->email_verified_at ?? now())
                : $user->email_verified_at,
            'phone_verified_at' => in_array($verification->channel, [OtpChannel::Sms, OtpChannel::WhatsApp], true)
                ? ($user->phone_verified_at ?? now())
                : $user->phone_verified_at,
        ])->save();

        return true;
    }

    protected function dispatch(OtpChannel $channel, string $destination, string $code, User $user): void
    {
        match ($channel) {
            OtpChannel::Email => Mail::to($destination)->send(new OtpCodeMail($user, $code)),
            OtpChannel::Sms => $this->sendViaTermii($destination, $code, 'sms'),
            OtpChannel::WhatsApp => $this->sendViaTermii($destination, $code, 'whatsapp'),
        };
    }

    protected function sendViaTermii(string $destination, string $code, string $channel): void
    {
        $apiKey = config('services.termii.key');

        if (blank($apiKey)) {
            if (app()->environment('local', 'testing')) {
                logger()->info('OTP dispatched (Termii not configured)', [
                    'channel' => $channel,
                    'destination' => $destination,
                    'code' => $code,
                ]);

                return;
            }

            throw new InvalidArgumentException('Termii API key is not configured.');
        }

        Http::baseUrl((string) config('services.termii.base_url'))
            ->post('/api/sms/send', [
                'api_key' => $apiKey,
                'to' => ltrim($destination, '+'),
                'from' => config('services.termii.sender_id'),
                'sms' => "Your Lagos Grassroots Alliance verification code is {$code}",
                'type' => 'plain',
                'channel' => $channel === 'whatsapp' ? 'whatsapp' : 'generic',
            ])
            ->throw();
    }
}
