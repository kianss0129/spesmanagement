<?php

namespace App\Http\Controllers;

use App\Models\Beneficiary;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;


class GoogleController extends Controller
{
    public function redirect()
    {
        $role = request()->query('role');
        if (in_array($role, ['Employer', 'Beneficiary'])) {
            request()->session()->put('socialite_role', $role);
        }


        return Socialite::driver('google')->redirect();
    }


    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $socialiteRole = request()->session()->pull('socialite_role');


            $user = User::firstOrCreate(
                [
                    'email' => $googleUser->email,
                ],
                [
                    'name' => $googleUser->name,
                    'password' => bcrypt(Str::random(16)),
                    'email_verified_at' => now(),
                ]
            );


            if (!$user->hasAnyRole([
                'Beneficiary',
                'Employer',
                'Admin',
                'PESO',
                'PESO Admin'
            ])) {
                if ($socialiteRole === 'Employer') {
                    $user->assignRole('Employer');
                    // Create employer profile for new Google OAuth employers
                    if (!$user->employer) {
                        Employer::create([
                            'user_id' => $user->id,
                            'company_name' => $googleUser->name,
                            'email' => $googleUser->email,
                            'phone' => null,
                            'address' => null,
                            'approval_status' => 'pending',
                        ]);
                    }
                } else {
                    $user->assignRole('Beneficiary');
                }
            }


            Auth::login($user, remember: true);
            request()->session()->regenerate();


            if ($user->hasRole('Employer')) {
                return redirect()->route('employer.dashboard');
            }


            if ($user->hasRole('Beneficiary')) {
                $beneficiary = $user->beneficiary;


                if (!$beneficiary) {
                    return redirect()
                        ->route('onboarding', ['category' => $socialiteRole === 'Employer' ? 'employer' : ($user->beneficiary_type ?? 'student')])
                        ->with('success', 'Welcome! Please complete your profile.');
                }


                if ($beneficiary->approval_status !== 'approved') {
                    return redirect()
                        ->route('onboarding.pending')
                        ->with('status', 'Your profile is pending approval.');
                }


                return redirect()->route('beneficiary.dashboard');
            }


            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            Log::error('Google OAuth callback error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Authentication failed. Please try again.');
        }
    }
}
