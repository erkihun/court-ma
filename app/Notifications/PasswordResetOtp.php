<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class PasswordResetOtp extends Notification
{
    public const EXPIRES_IN_MINUTES = 10;

    public function __construct(protected readonly string $code) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $name = ($notifiable->full_name ?? '') ?: ($notifiable->name ?? '');

        return (new MailMessage)
            ->subject('የይለፍ ቃል መቀየሪያ ኮድ')
            ->view(['mail.password-reset-code', 'mail.password-reset-code-text'], [
                'name' => $name,
                'code' => $this->code,
                'minutes' => self::EXPIRES_IN_MINUTES,
            ]);
    }
}
