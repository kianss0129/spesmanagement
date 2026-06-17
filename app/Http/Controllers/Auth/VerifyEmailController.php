<?php


namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;


class VerifyEmailController extends Controller
{
    /**
     * Handle the email verification link.
     */
    public function __invoke($id, $hash)
    {
        $user = User::findOrFail($id);


        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Invalid or expired verification link.']);
        }


        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }


        if (! Auth::check()) {
            Auth::login($user);
        }


        if ($user->hasRole('Employer')) {
            return redirect()->route('employer.dashboard', ['verified' => 1]);
        }


        if ($user->hasRole('Beneficiary')) {
            return redirect()->route('beneficiary.dashboard', ['verified' => 1]);
        }


        return redirect()->route('dashboard', ['verified' => 1]);
    }
}
