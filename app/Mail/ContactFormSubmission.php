<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactFormSubmission extends Mailable
{
    use Queueable, SerializesModels;

    public $contactData;

    public function __construct($contactData)
    {
        $this->contactData = $contactData;
    }

    public function build()
    {
        return $this->markdown('emails.contact-form-submission')
                    ->subject('New Contact Form Submission')
                    ->with([
                        'name' => $this->contactData['name'],
                        'email' => $this->contactData['email'],
                        'subject' => $this->contactData['subject'],
                        'message' => $this->contactData['message']
                    ]);
    }
}