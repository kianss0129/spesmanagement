<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContractSigningMail extends Mailable
{
    use Queueable, SerializesModels;

    public $application;

    public function __construct($application)
    {
        $this->application = $application;
    }

    public function build()
    {
        return $this->subject('SPES Contract Signing Schedule')
            ->view('emails.contract-signing');
    }
}