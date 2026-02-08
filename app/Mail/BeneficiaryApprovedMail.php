<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BeneficiaryApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $password;
    public $loginUrl;

    public function __construct($name, $password)
    {
        $this->name = $name;
        $this->password = $password;
        $this->loginUrl = route('login');
    }

    public function build()
    {
        return $this->subject('Your SPES Account Has Been Approved')
                    ->view('emails.beneficiary-approved');
    }
}
