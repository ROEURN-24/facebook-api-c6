<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $resetLink;
    public $expires;

    /**
     * Create a new message instance.
     *
     * @param  User  $user
     * @param  string  $token
     * @param  \DateTime|null  $expires
     * @return void
     */
    public function __construct(User $user, string $token, ?\DateTime $expires = null)
    {
        $this->user = $user;
        $this->resetLink = url('/password/reset', ['token' => $token]); // Correct way to pass parameters to url()
        $this->expires = $expires;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.reset_password')
            ->subject('Reset Your Password'); // Optional: You can set a custom subject here
    }
}
