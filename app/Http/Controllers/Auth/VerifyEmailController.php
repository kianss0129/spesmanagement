<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Log;

class VerifyEmailController extends Controller
{
    public function __invoke($id, $hash)
    {
        try {
            $user = \App\Models\User::findOrFail($id);

            if ($user->hasVerifiedEmail()) {
                return redirect()->route('dashboard');
            }

            if ($user->getEmailForVerification() === $hash) {
                $user->markEmailAsVerified();
                event(new Verified($user));

                return redirect()->route('dashboard')->with('status', 'Email verified!');
            }

            return redirect()->route('login')->withErrors(['email' => 'Invalid verification link.']);
        } catch (\Exception $e) {
            Log::error('Error verifying email: ' . $e->getMessage());
            return back()->withErrors(['email' => 'An error occurred during verification.']);
        }
    }
}
