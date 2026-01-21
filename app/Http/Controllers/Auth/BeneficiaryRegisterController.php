<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Auth\Events\Registered;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class BeneficiaryRegisterController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/RegisterBeneficiary');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','confirmed', Password::min(8)->letters()->numbers()],
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

            // 4. Fire registered event
            event(new Registered($user));
        });

        // 5. Auto-login
        Auth::login($user);

        // ✅ 6. Redirect to onboarding with category
        return redirect()->route('onboarding', [
            'category' => $user->beneficiary_type
        ]);
    }
}
