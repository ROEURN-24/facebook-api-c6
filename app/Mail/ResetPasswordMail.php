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
    public $otp;
    public $expires;

    /**
     * Create a new message instance.
     *
     * @param  User  $user
     * @param  int  $otp
     * @param  string  $expires
     * @return void
     */
    public function __construct(User $user, int $otp, string $expires)
    {
        $this->user = $user;
        $this->otp = $otp;
        $this->expires = $expires;
    }

    public function build()
    {
        return $this->from('roeurnkaki@gmail.com', 'Artificial Reality')
            ->view('emails.reset_password')
            ->subject('Reset Your Password')
            ->with([
                'otp' => $this->otp,
                'expires' => $this->expires,
            ]);
    }
}
