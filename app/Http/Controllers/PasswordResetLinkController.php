<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetLinkController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
        ]);

        $user = User::findForLogin($request->string('login')->toString());

        if (! $user) {
            throw ValidationException::withMessages([
                'login' => __('We cannot find a user with that email or phone number.'),
            ]);
        }

        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'login' => __($status),
            ]);
        }

        return back()->with('status', __('If an account exists, a reset link has been sent to the email on file.'));
    }
}
