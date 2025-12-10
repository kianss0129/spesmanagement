<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\EmailVerificationNotification;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->user()->sendEmailVerificationNotification();
            return back()->with('status', 'Verification link sent!');
        } catch (\Exception $e) {
            Log::error('Error sending verification email: ' . $e->getMessage());
            return back()->withErrors(['email' => 'An error occurred while sending the verification link.']);
        }
    }
}
