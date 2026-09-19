<?php

namespace App\Services;

use App\Enums\OtpPurpose;
use App\Models\User;
use App\Notifications\OtpCodeNotification;
use App\Services\Contracts\OtpNotifierInterface;

/**
 * Sends the OTP code by email. This is the default channel for the
 * foundation stage — swap the binding in AppServiceProvider for an
 * SMS/WhatsApp notifier later without touching OtpService.
 */
class MailOtpNotifier implements OtpNotifierInterface
{
    public function send(User $user, string $code, OtpPurpose $purpose): void
    {
        $user->notify(new OtpCodeNotification($code, $purpose));
    }
}
