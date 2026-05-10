<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ForgotPasswordOtpController extends Controller
{
    // STEP 1: Send OTP
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $otp = rand(100000, 999999);

        DB::table('password_otp_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'otp' => Hash::make($otp),
                'expires_at' => Carbon::now()->addMinutes(10),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        // Send Email
        Mail::raw("Your password reset OTP is: $otp. It expires in 10 minutes.", function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('Password Reset OTP');
        });

        return back()->with('success', 'OTP sent successfully.');
    }

    // STEP 2: Verify OTP + Reset Password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $record = DB::table('password_otp_resets')
                    ->where('email', $request->email)
                    ->first();

        if (!$record) {
            return back()->withErrors(['otp' => 'Invalid OTP']);
        }

        // Check expiration
        if (Carbon::now()->gt($record->expires_at)) {
            return back()->withErrors(['otp' => 'OTP expired']);
        }

        // Check OTP
        if (!Hash::check($request->otp, $record->otp)) {
            return back()->withErrors(['otp' => 'Invalid OTP']);
        }

        // Update password
        User::where('email', $request->email)
            ->update([
                'password' => Hash::make($request->password)
            ]);

        // Delete OTP record
        DB::table('password_otp_resets')
            ->where('email', $request->email)
            ->delete();

        return redirect('/login')->with('success', 'Password reset successfully.');
    }
}
