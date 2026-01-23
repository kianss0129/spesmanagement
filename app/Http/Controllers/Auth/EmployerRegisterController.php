<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EmployerRegisterController extends Controller
{
    // Show registration page
    public function create(Request $request)
    {
        // Support both Inertia & Blade
        if (!$request->header('X-Inertia')) {
            return view('auth.register-employer');
        }

        return Inertia::render('Auth/RegisterEmployer');
    }

    // Handle registration form submission
    public function store(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'company_name'  => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'      => ['required', 'confirmed', 'min:8'],
        ]);

        $user = null;

        DB::transaction(function () use ($validated, &$user) {

            // 1️⃣ Create User
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // 2️⃣ Assign Employer role (Spatie)
            $role = Role::firstOrCreate(['name' => 'Employer']);
            $user->assignRole($role);

            // 3️⃣ Create Employer profile via relationship
            $user->employer()->create([
                'company_name'            => $validated['company_name'],
                'phone'                   => null,
                'address'                 => null,
                'onboarding_completed_at' => null,
                'approved'                => false, // needs PESO approval
            ]);

            // 4️⃣ Fire email verification (if used)
            event(new Registered($user));
        });

        // 5️⃣ Auto login
        Auth::login($user);

        // 6️⃣ Redirect to Employer Onboarding
        return redirect()->route('onboarding', [
            'category' => 'employer',
        ]);
    }
}
