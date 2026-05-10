<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\TemporaryPasswordMail; // Import the Mailable

class UserApprovalController extends Controller
{
    public function approve(Request $request, $userId)
    {
        // Find the user
        $user = User::findOrFail($userId);

        // Generate a temporary password (10 chars)
        $tempPassword = Str::random(10);

        // Save hashed password & mark as temporary
        $user->password = Hash::make($tempPassword);
        $user->is_temporary_password = true;
        $user->status = 'approved';
        $user->save();

        // Send email with temporary password using Mailable
        $loginLink = url('/login'); // Replace if your login route is different
        Mail::to($user->email)->send(new TemporaryPasswordMail($user->name, $tempPassword, $loginLink));

        return redirect()->back()->with('success', 'User approved and email sent successfully.');
    }
}
