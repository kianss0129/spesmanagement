<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user->hasRole('Admin')) {
            return redirect()->intended('/dashboard');
        }

        if ($user->hasRole('PESO')) {
            return redirect()->intended('/dashboard');
        }

        if ($user->hasRole('Employer')) {
            return redirect()->intended('/dashboard');
        }

        if ($user->hasRole('Beneficiary')) {
            return redirect()->intended('/dashboard');
        }

        return redirect()->intended('/');
    }
}
