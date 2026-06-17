<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BeneficiaryExamScheduleMail extends Mailable
{
    use Queueable, SerializesModels;

    public $beneficiary;
    public $exam_date;
    public $exam_time;
    public $exam_venue;

    public function __construct($beneficiary, $exam_date, $exam_time, $exam_venue)
    {
        $this->beneficiary = $beneficiary;
        $this->exam_date = $exam_date;
        $this->exam_time = $exam_time;
        $this->exam_venue = $exam_venue;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'SPES Examination Schedule',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.beneficiary-exam-schedule',
            with: [
                'beneficiary' => $this->beneficiary,
                'exam_date' => $this->exam_date,
                'exam_time' => $this->exam_time,
                'exam_venue' => $this->exam_venue,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}