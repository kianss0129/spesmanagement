<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Inertia\Inertia; // ✅ Add this line

class EmailVerificationPromptController extends Controller
{
    public function __invoke()
    {
        if (auth()->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        // Use Inertia instead of Blade
        return Inertia::render('Auth/VerifyEmail', [
            'status' => session('status'),
        ]);
    }
}
