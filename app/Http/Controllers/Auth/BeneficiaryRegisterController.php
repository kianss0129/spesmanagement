<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Mail\WelcomeEmail;

class BeneficiaryRegisterController extends Controller
{
    // Show registration page
    public function create()
    {
        return Inertia::render('Auth/RegisterBeneficiary');
    }

    // Handle registration form submission
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','confirmed','min:8'],
            'type' => ['required','in:student,osy,dependent'],
        ]);

        $user = null;

        DB::transaction(function () use ($validated, &$user) {

            // 1. Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'beneficiary_type' => $validated['type'],
            ]);

            // 2. Assign Beneficiary role
            $role = Role::firstOrCreate(['name' => 'Beneficiary']);
            $user->assignRole($role);

            // 3. Create beneficiary profile
            $user->beneficiary()->create([
                'first_name' => $validated['name'],
                'last_name' => '',
                'email' => $validated['email'],
                'approved' => false,
                'approval_status' => 'pending',
            ]);

            // 4. Fire registered event (triggers verification email if configured)
            event(new Registered($user));
        });

        // 5. Auto-login
        Auth::login($user);

        // 6. Send welcome email using Mailable
        Mail::to($user->email)->send(new WelcomeEmail($user));

        // 7. Redirect to onboarding with category
        return redirect()->route('onboarding', [
            'category' => $user->beneficiary_type
        ]);
    }
}
