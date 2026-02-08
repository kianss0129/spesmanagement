<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EmployerRegisterController extends Controller
{
    // Show registration page
    public function create(Request $request)
    {
        // Always render the Inertia page so initial requests include the
        // Inertia page payload (avoids client-side null `component` errors).
        return Inertia::render('Auth/RegisterEmployer');
    }

    // Handle registration form submission
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'company_name'  => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'      => ['required', 'confirmed', 'min:8'],
        ]);

        $user = null;

        DB::transaction(function () use ($validated, &$user) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Assign Employer role
            $user->assignRole('Employer');

            // Create employer profile
            $user->employer()->create([
                'company_name'            => $validated['company_name'],
                'phone'                   => null,
                'address'                 => null,
                'onboarding_completed_at' => null,
                'approved'                => false,
            ]);

            event(new Registered($user));
        });

        // Log in the user
        Auth::login($user);

        // ✅ FIXED: Proper route parameter
        return redirect()->route('onboarding', ['category' => 'employer']);
    }
}
