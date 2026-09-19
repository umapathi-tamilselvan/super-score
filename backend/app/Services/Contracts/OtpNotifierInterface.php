<?php

namespace App\Services\Contracts;

use App\Enums\OtpPurpose;
use App\Models\User;

/**
 * Delivers an OTP code to a user through whatever channel the
 * implementation wraps (mail today, SMS/WhatsApp later) — OtpService
 * depends on this interface, never on a concrete channel, so the
 * delivery mechanism can change without touching OTP business rules.
 */
interface OtpNotifierInterface
{
    public function send(User $user, string $code, OtpPurpose $purpose): void;
}
