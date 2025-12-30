<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client as TwilioClient;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log; // ✅ Add this import
use App\Models\User;
use App\Mail\InterviewScheduledMail;

class NotificationService
{
    public function sendEmail($to, string $subject, string $view, array $data = []): void
    {
        Mail::send($view, $data, function ($m) use ($to, $subject) {
            $m->to($to)->subject($subject);
        });
    }

    public function sendSms(string $to, string $message): void
    {
        $sid = config('twilio.sid');
        $token = config('twilio.token');

        if (empty($sid) || empty($token)) {
            Log::warning('Twilio not configured. Skipping SMS to ' . $to); // ✅ use Log facade
            return;
        }

        $twilio = new TwilioClient($sid, $token);
        $twilio->messages->create($to, [
            'from' => config('twilio.from'),
            'body' => $message,
        ]);
    }

    public function pushDashboardNotification(int $userId, array $data): void
    {
        $user = User::find($userId);
        if (! $user) return;

        Notification::send($user, new \App\Notifications\CustomNotification($data));
    }

    public static function sendInterviewNotification($beneficiary, $interview)
    {
        try {
            Mail::to($beneficiary->email)->send(new InterviewScheduledMail($interview));
        } catch (\Throwable $e) {
            Log::error('Interview email failed: ' . $e->getMessage());
        }

        try {
            $svc = new self();
            if (!empty($beneficiary->phone)) {
                $svc->sendSms($beneficiary->phone, 'Your SPES interview is scheduled. Join here: ' . $interview->meet_link);
            }
        } catch (\Throwable $e) {
            Log::error('Interview SMS failed: ' . $e->getMessage());
        }

        try {
            $svc = new self();
            if (property_exists($beneficiary, 'user') && $beneficiary->user) {
                $svc->pushDashboardNotification($beneficiary->user->id, [
                    'title' => 'Interview Scheduled',
                    'message' => 'Your interview is scheduled on ' . $interview->scheduled_at,
                    'link' => $interview->meet_link
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Dashboard notification failed: ' . $e->getMessage());
        }
    }
}
