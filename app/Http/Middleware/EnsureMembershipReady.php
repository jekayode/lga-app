<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMembershipReady
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($request->routeIs('otp.verify', 'two-factor.setup', 'logout', 'verification.*', 'two-factor.*', 'password.*')) {
            return $next($request);
        }

        if (! $user->hasCompletedRequiredOtp()) {
            return redirect()->route('otp.verify');
        }

        if (! $user->hasCompletedMandatoryTwoFactor()) {
            return redirect()->route('two-factor.setup');
        }

        if (! $user->is_active) {
            auth()->logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been deactivated.',
            ]);
        }

        return $next($request);
    }
}
