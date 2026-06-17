<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Spatie\Permission\Models\Role;

class PESORegisterController extends Controller
{
    public function create(\Illuminate\Http\Request $request)
    {
        if (! $request->header('X-Inertia')) {
            return view('auth.register-peso');
        }

        return inertia('Auth/RegisterPESO');
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
            'password' => Hash::make($request->password),
            'role' => 'peso',
        ]);

        // Ensure Spatie role exists then assign it
        Role::firstOrCreate(['name' => 'PESO']);
        $user->assignRole('PESO');

        event(new Registered($user)); // ✅ Email verification

        Auth::login($user);

        return redirect()->route('peso.user.dashboard');
    }
}
