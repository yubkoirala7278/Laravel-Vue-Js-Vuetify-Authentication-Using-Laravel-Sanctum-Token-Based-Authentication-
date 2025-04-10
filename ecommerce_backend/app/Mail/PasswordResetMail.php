<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;
    public $token;
    public $email;
    /**
     * Create a new message instance.
     *
     * @param string $token
     * @param string $email
     * @return void
     */
    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000'); // Fallback to localhost:3000 if not set
        return $this->subject('Reset Your Password')
            ->view('emails.reset')
            ->with([
                'token' => $this->token,
                'email' => $this->email,
                'frontendUrl' => $frontendUrl,
            ]);
    }
}
