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

        try {
            // Capture DB queries that occur during the sendResetLink call so we can see any token inserts
            $queries = [];
            \Illuminate\Support\Facades\DB::listen(function ($query) use (&$queries) {
                $queries[] = ['sql' => $query->sql, 'bindings' => $query->bindings, 'time' => $query->time];
            });

            $status = Password::sendResetLink(
                $request->only('email')
            );

            // Log the attempt for local debugging and include any queries observed
            Log::info('Password reset requested', ['email' => $request->email, 'status' => $status, 'queries' => $queries]);

        } catch (\Throwable $e) {
            Log::error('Password reset failed', ['email' => $request->email, 'exception' => $e->getMessage()]);

            if ($request->wantsJson()) {
                return response()->json(['error' => 'Failed to send reset link.'], 500);
            }

            return back()->withErrors(['email' => 'Failed to send reset link. Please try again later.']);
        }

        // If this was an XHR / JSON request, return JSON so the SPA can handle it more easily
        if ($request->wantsJson()) {
            return response()->json(['status' => __($status)]);
        }

        return back()->with('status', __($status));
    }
}
