<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PasswordResetLinkController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function store(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Log the attempt for local debugging
        Log::info('Password reset requested', ['email' => $request->email, 'status' => $status]);

        // If this was an XHR / JSON request, return JSON so the SPA can handle it more easily
        if ($request->wantsJson()) {
            return response()->json(['status' => __($status)]);
        }

        return back()->with('status', __($status));
    }
}
