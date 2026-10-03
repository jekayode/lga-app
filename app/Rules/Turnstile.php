<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class Turnstile implements ValidationRule
{
    public function __construct(private ?string $expectedAction = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secret = config('services.turnstile.secret_key');

        if (blank($secret)) {
            if (app()->environment('local', 'testing')) {
                return;
            }

            $fail('Cloudflare Turnstile is not configured.');

            return;
        }

        if (! is_string($value) || blank($value)) {
            $fail('Please complete the security check.');

            return;
        }

        try {
            $response = Http::asForm()->timeout(5)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $secret,
                'response' => $value,
                'remoteip' => request()->ip(),
            ]);
        } catch (ConnectionException) {
            $fail('The security check could not be verified. Please try again.');

            return;
        }

        if (! $response->json('success')) {
            $fail('Security check failed. Please try again.');

            return;
        }

        if ($this->expectedAction !== null && $response->json('action') !== $this->expectedAction) {
            $fail('Security check failed. Please try again.');

            return;
        }

        $allowedHostnames = config('services.turnstile.allowed_hostnames', []);

        if ($allowedHostnames !== [] && ! in_array($response->json('hostname'), $allowedHostnames, true)) {
            $fail('Security check failed. Please try again.');
        }
    }
}
