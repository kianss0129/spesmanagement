<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TemporaryPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $tempPassword;
    public $loginLink;

    public function __construct($name, $tempPassword, $loginLink)
    {
        $this->name = $name;
        $this->tempPassword = $tempPassword;
        $this->loginLink = $loginLink;
    }

    public function build()
    {
        return $this->subject('Your Account is Approved – Temporary Password')
                    ->view('emails.temporary_password');
    }
}
