<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExamResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public $exam;
    public $result;

    public function __construct($exam, $result)
    {
        $this->exam = $exam;
        $this->result = $result;
    }

    public function build()
    {
        return $this->subject('SPES Exam Result')
            ->view('emails.exam-result');
    }
}