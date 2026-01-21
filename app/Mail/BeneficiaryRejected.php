<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BeneficiaryRejected extends Mailable
{
    use Queueable, SerializesModels;

    public $beneficiary;

    public function __construct($beneficiary)
    {
        $this->beneficiary = $beneficiary;
    }

    public function build()
    {
        return $this->subject('SPES Documents Rejected')
                    ->view('emails.beneficiary-rejected');
    }
}
