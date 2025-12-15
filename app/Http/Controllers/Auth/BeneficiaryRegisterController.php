<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;

class BeneficiaryRegisterController extends Controller
{
    public function create()
    {
        return inertia('Auth/RegisterBeneficiary');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => 'beneficiary', // ✅ AUTO ROLE
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user)); // ✅ Email verification

        Auth::login($user);

        return redirect()->route('beneficiary.dashboard');
    }
}
