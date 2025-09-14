<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends BaseVerifyEmail
{
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Please verify your email address')
            ->greeting('Welcome!')
            ->line('Click the button below to verify your email.')
            ->action('Verify Email', $verificationUrl)
            ->line('If you didn’t create an account, no action is required.');
    }
}
