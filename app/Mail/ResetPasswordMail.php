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
    public function __construct(User $user, string $token, string $expiresAt)
    {
        $this->user = $user;
        $this->resetLink = $token;
        $this->expires = $expiresAt;
    }

    public function build()
    {
        $resetUrl = url('/password/reset/' . $this->resetLink);

        return $this->from('roeurnkaki@gmail.com', 'Artificial Reality')
            ->view('emails.reset_password')
            ->subject('Reset Your Password')
            ->with([
                'resetLink' => $resetUrl,
                'expires' => $this->expires,
            ]);
    }


}
