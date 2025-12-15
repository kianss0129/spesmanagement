<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use App\Models\User;

class VerifyEmailController extends Controller
{
    /**
     * Handle the email verification link.
     *
     * This expects the signed URL with {id} and {hash} (sha1 of the email)
     * Example route: /email/verify/{id}/{hash}
     *
     * @param  int  $id
     * @param  string $hash
     */
    public function __invoke($id, $hash)
    {
        $user = User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        // The verification hash in the signed URL is sha1($user->getEmailForVerification())
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('login')->withErrors(['email' => 'Invalid or expired verification link.']);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        // redirect to the appropriate dashboard or route after verification
        return redirect()->route('dashboard')->with('status', 'Email verified!');
    }
}
