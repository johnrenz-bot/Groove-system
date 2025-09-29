<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AdminPasscodeNotification extends Notification
{
    use Queueable;

    public function __construct(public string $code) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Admin Verification Code')
            ->greeting('Hi ' . ($notifiable->name ?? 'Admin'))
            ->line('Use this 6-digit code to complete your sign-in:')
            ->line("**{$this->code}**")
            ->line('This code expires in 10 minutes.');
    }
}
