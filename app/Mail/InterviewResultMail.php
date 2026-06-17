<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InterviewResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public $interview;
    public $result;

    public function __construct($interview, $result)
    {
        $this->interview = $interview;
        $this->result = $result;
    }

    public function build()
    {
        return $this->subject('SPES Interview Result')
            ->view('emails.interview-result');
    }
}