<?php

namespace App\Services\Contracts;

use App\Enums\OtpPurpose;
use App\Models\OtpCode;
use App\Models\User;

interface OtpServiceInterface
{
    /**
     * Generate a fresh OTP code for the user and dispatch it via the
     * configured notifier, invalidating any previous unconsumed code
     * for the same purpose.
     */
    public function generateAndSend(User $user, OtpPurpose $purpose = OtpPurpose::Registration): OtpCode;

    /**
     * Verify a submitted code against the user's latest unconsumed,
     * unexpired code for the given purpose.
     */
    public function verify(User $user, string $code, OtpPurpose $purpose = OtpPurpose::Registration): bool;

    /**
     * Re-issue a new OTP code for the user, superseding any pending one.
     */
    public function resend(User $user, OtpPurpose $purpose = OtpPurpose::Registration): OtpCode;
}
