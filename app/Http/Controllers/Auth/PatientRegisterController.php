<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class PatientRegisterController extends Controller
{
    /** Show the custom patient‑only sign‑up page. */
    public function create()
    {
        return Inertia::render('Auth/PatientRegister');
    }

    /** Handle the submission. */
    public function store(Request $request)
    {
        // ---------- 1. Validate ------------------------------------------------
        $rules = [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        if (config('services.nocaptcha.sitekey') && config('services.nocaptcha.secret')) {
            $rules['g-recaptcha-response'] = ['required', 'captcha'];
        }

        $validated = $request->validate($rules);

        // ---------- 2. Create user & role -------------------------------------
        $user = $this->createUser($validated);

        // ---------- 3. Fire “Registered” (so Jetstream emails verify link) ----
        event(new Registered($user));

        // ---------- 4. LOG THE USER IN *before* redirecting -------------------
        auth()->login($user);

        // ---------- 5. Send them to the verify‑email screen, **not** login ----
        return redirect()->route('verification.notice');
    }

    /** Create the user and assign role */
    protected function createUser($validated)
    {
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole('Patient'); // Assign 'Patient' role

        return $user;
    }
}

        