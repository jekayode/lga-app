<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        $user = $request->user();

        if ($user && ! $user->hasCompletedRequiredOtp()) {
            return redirect()->route('otp.verify');
        }

        if ($user && ! $user->hasCompletedMandatoryTwoFactor()) {
            return redirect()->route('two-factor.setup');
        }

        return redirect()->intended(config('fortify.home'));
    }
}
