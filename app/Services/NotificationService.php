<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client as TwilioClient;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Mail\InterviewScheduledMail;

class NotificationService
{
    /**
     * Send Email using a blade view or Mailable (simple wrapper).
     *
     * @param string|array $to
     * @param string $subject
     * @param string $view
     * @param array $data
     * @return void
     */
    public function sendEmail($to, string $subject, string $view, array $data = []): void
    {
        Mail::send($view, $data, function ($m) use ($to, $subject) {
            $m->to($to)->subject($subject);
        });
    }

    /**
     * Send SMS via Twilio.
     *
     * @param string $to E.164 format e.g. +63917xxxxxxx
     * @param string $message
     * @return void
     */
    public function sendSms(string $to, string $message): void
    {
        $sid = config('twilio.sid');
        $token = config('twilio.token');

        if (empty($sid) || empty($token)) {
            // Twilio not configured — skip silently or log
            \Log::warning('Twilio not configured. Skipping SMS to ' . $to);
            return;
        }

        $twilio = new TwilioClient($sid, $token);
        $twilio->messages->create($to, [
            'from' => config('twilio.from'),
            'body' => $message,
        ]);
    }

    /**
     * Push dashboard (database) notification using Laravel Notifications.
     */
    public function pushDashboardNotification(int $userId, array $data): void
    {
        $user = User::find($userId);
        if (! $user) {
            return;
        }

        // Example: using a CustomNotification that accepts an array payload
        Notification::send($user, new \App\Notifications\CustomNotification($data));
    }

    /**
     * Convenience static method used to notify beneficiary about scheduled interview.
     */
    public static function sendInterviewNotification($beneficiary, $interview)
    {
        // Use Mailable
        try {
            Mail::to($beneficiary->email)->send(new InterviewScheduledMail($interview));
        } catch (\Throwable $e) {
            \Log::error('Interview email failed: ' . $e->getMessage());
        }

        // SMS (best effort)
        try {
            $svc = new self();
            if (!empty($beneficiary->phone)) {
                $svc->sendSms($beneficiary->phone, 'Your SPES interview is scheduled. Join here: ' . $interview->meet_link);
            }
        } catch (\Throwable $e) {
            \Log::error('Interview SMS failed: ' . $e->getMessage());
        }

        // Dashboard notification (best effort)
        try {
            $svc = new self();
            if (property_exists($beneficiary, 'user') && $beneficiary->user) {
                // if Beneficiary is related to a User model
                $svc->pushDashboardNotification($beneficiary->user->id, [
                    'title' => 'Interview Scheduled',
                    'message' => 'Your interview is scheduled on ' . $interview->scheduled_at,
                    'link' => $interview->meet_link
                ]);
            } elseif (method_exists($beneficiary, 'getKey')) {
                // fallback: if beneficiary has no user relation, maybe user id stored elsewhere
                // skip
            }
        } catch (\Throwable $e) {
            \Log::error('Dashboard notification failed: ' . $e->getMessage());
        }
    }
}
