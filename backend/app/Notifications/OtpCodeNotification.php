<?php

namespace App\Notifications;

use App\Enums\OtpPurpose;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class OtpCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $code,
        public readonly OtpPurpose $purpose,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Super Score verification code')
            ->greeting('Verify your account')
            ->line('Use the following code to verify your Super Score account:')
            ->line(new HtmlString("<h2 style=\"letter-spacing: 4px;\">{$this->code}</h2>"))
            ->line('This code expires in '.config('otp.ttl_minutes').' minutes.')
            ->line('If you did not request this code, you can safely ignore this email.');
    }
}
