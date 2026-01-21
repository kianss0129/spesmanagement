<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EmployerRegisterController extends Controller
{
    public function create(Request $request)
    {
        // Support both Inertia & Blade (safe)
        if (! $request->header('X-Inertia')) {
            return view('auth.register-employer');
        }

        return Inertia::render('Auth/RegisterEmployer');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['required','confirmed','min:8'],
        ]);

        $user = null;

        DB::transaction(function () use ($validated, &$user) {

            // 1️⃣ Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // 2️⃣ Assign Employer role (Spatie)
            $role = Role::firstOrCreate(['name' => 'Employer']);
            $user->assignRole($role);

            // 3️⃣ Fire email verification
            event(new Registered($user));
        });

        // 4️⃣ Auto login
        Auth::login($user);

        // ✅ 5️⃣ Redirect to ONBOARDING (Employer)
        return redirect()->route('onboarding', [
            'category' => 'employer',
        ]);
    }
}
