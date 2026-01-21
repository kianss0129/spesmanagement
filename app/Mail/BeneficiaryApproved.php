<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BeneficiaryApproved extends Mailable
{
    use Queueable, SerializesModels;

    public $beneficiary;
    public $password;

    /**
     * Create a new message instance.
     */
    public function __construct($beneficiary, $password = null)
    {
        $this->beneficiary = $beneficiary;
        $this->password = $password; // optional, only if you want to send a temporary password
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('SPES Account Approved')
                    ->view('emails.beneficiary-approved')
                    ->with([
                        'name' => $this->beneficiary->name,
                        'email' => $this->beneficiary->email,
                        'password' => $this->password,
                        'dashboardUrl' => url('/dashboard'),
                    ]);
    }
}
