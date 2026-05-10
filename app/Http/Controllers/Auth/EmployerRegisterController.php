<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use App\Mail\WelcomeEmail;

class EmployerRegisterController extends Controller
{
    // Show registration page
    public function create()
    {
        return Inertia::render('Auth/RegisterEmployer');
    }

    // Handle registration form submission
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'company_name'  => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255','unique:users,email'],
            'password'      => ['required','confirmed','min:8'],
        ]);

        $user = null;

        DB::transaction(function () use ($validated, &$user) {
            // 1. Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // 2. Assign Employer role
            $role = Role::firstOrCreate(['name' => 'Employer']);
            $user->assignRole($role);

            // 3. Create employer profile
            $user->employer()->create([
                'company_name'            => $validated['company_name'],
                'phone'                   => null,
                'address'                 => null,
                'onboarding_completed_at' => null,
                'approved'                => false,
            ]);

            // 4. Fire registered event (triggers verification email if configured)
            event(new Registered($user));
        });

        // 5. Auto-login
        Auth::login($user);

        // 6. Send welcome email using Mailable
        Mail::to($user->email)->send(new WelcomeEmail($user));

        // 7. Redirect to onboarding
        return redirect()->route('onboarding', ['category' => 'employer']);
    }
}
