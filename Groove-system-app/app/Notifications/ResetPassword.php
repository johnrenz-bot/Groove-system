<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // optional but recommended
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends Notification implements ShouldQueue
{
    use Queueable;

    public string $token;
    public string $userType;

    public function __construct(string $token, string $userType)
    {
        $this->token    = $token;
        $this->userType = $userType; // 'client' | 'coach' | 'admin'
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $userName = $notifiable->firstname ?? $notifiable->name ?? 'User';

        // /reset-password/{user_type}/{token}?email=...
        $url = route('reset-password.form', [
            'user_type' => $this->userType,
            'token'     => $this->token,
        ]) . '?email=' . rawurlencode($notifiable->email);

        $minutes = 60; // keep in sync with controller validation

        return (new MailMessage)
            ->subject('Groove Password Reset')
            ->greeting("Hello, {$userName}!")
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $url)
            ->line("This password reset link will expire in {$minutes} minutes.")
            ->line('If you did not request a password reset, no further action is required.');
    }
}
