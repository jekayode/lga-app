<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class AuthenticateUser
{
    public function __invoke(Request $request): ?User
    {
        $login = (string) $request->input(Fortify::username(), $request->input('email', ''));
        $password = (string) $request->input('password', '');

        $user = User::findForLogin($login);

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                Fortify::username() => 'Your account has been deactivated.',
            ]);
        }

        return $user;
    }
}
