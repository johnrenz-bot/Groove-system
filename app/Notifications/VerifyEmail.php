<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmail extends Notification
{
    use Queueable;

    protected string $code;
    protected string $userType;

    public function __construct(string $code, string $userType = 'client')
    {
        $this->code = $code;
        $this->userType = $userType;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $userName = trim(($notifiable->firstname ?? '') . ' ' . ($notifiable->lastname ?? ''));
        if ($userName === '') {
            $userName = 'User';
        }

        // Piliin ang tamang verification route batay sa uri
        $verificationUrl = $this->userType === 'coach'
            ? route('coach.verify-email', ['code' => $this->code])
            : route('verify-email', ['code' => $this->code]);

        $mail = (new MailMessage)
            ->subject('Verify Your Email')
            ->greeting("Hello, {$userName}!")
            ->line('Please verify your email address by clicking the button below.')
            ->action('Verify Email', $verificationUrl);

        // ➜ Extra paragraph kapag Coach ang nag-register
        if ($this->userType === 'coach') {
            $mail->line('After verifying your email, our team will carefully review the information and documents you submitted. ' .
                        'This review typically takes **1–5 business days** to ensure that your profile, credentials, and uploaded IDs are valid and complete.');
            $mail->line('Once the verification process is successful, your Groove coach/performer account will be fully activated and visible to potential clients and artists.');
        }

        return $mail->line('Thank you for using Groove!');
    }
}
