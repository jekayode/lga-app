<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class Turnstile implements ValidationRule
{
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

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $secret,
            'response' => $value,
            'remoteip' => request()->ip(),
        ]);

        if (! $response->json('success')) {
            $fail('Security check failed. Please try again.');
        }
    }
}
