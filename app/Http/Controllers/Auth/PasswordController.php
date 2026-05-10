<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function showChangeForm() {
        return view('auth.change-password');
    }

    public function updatePassword(Request $request) {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->is_temporary_password = false;
        $user->save();

        return redirect('/dashboard')->with('success', 'Password changed successfully.');
    }
}
