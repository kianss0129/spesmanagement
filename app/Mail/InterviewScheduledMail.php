<?php

namespace App\Mail;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InterviewScheduledMail extends Mailable
{
    use Queueable, SerializesModels;

    public Interview $interview;

    /**
     * Create a new message instance.
     *
     * @param Interview $interview
     */
    public function __construct(Interview $interview)
    {
        $this->interview = $interview;
    }

    public function build()
    {
        return $this->subject('SPES Interview Scheduled')
                    ->view('emails.interview-scheduled')
                    ->with([
                        'interview' => $this->interview,
                    ]);
    }
}
