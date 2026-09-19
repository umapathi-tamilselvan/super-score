<?php

namespace App\Services;

use App\Enums\OtpPurpose;
use App\Models\OtpCode;
use App\Models\User;
use App\Services\Contracts\OtpNotifierInterface;
use App\Services\Contracts\OtpServiceInterface;

class OtpService implements OtpServiceInterface
{
    public function __construct(
        private readonly OtpNotifierInterface $notifier,
    ) {}

    public function generateAndSend(User $user, OtpPurpose $purpose = OtpPurpose::Registration): OtpCode
    {
        $this->invalidatePending($user, $purpose);

        $code = str_pad(
            (string) random_int(0, (10 ** config('otp.length')) - 1),
            config('otp.length'),
            '0',
            STR_PAD_LEFT
        );

        $otpCode = $user->otpCodes()->create([
            'code' => $code,
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(config('otp.ttl_minutes')),
        ]);

        $this->notifier->send($user, $code, $purpose);

        return $otpCode;
    }

    public function verify(User $user, string $code, OtpPurpose $purpose = OtpPurpose::Registration): bool
    {
        $otpCode = $this->latestPending($user, $purpose);

        if (! $otpCode || $otpCode->isExpired() || ! hash_equals($otpCode->code, $code)) {
            return false;
        }

        $otpCode->update(['consumed_at' => now()]);

        if ($purpose === OtpPurpose::Registration && ! $user->hasVerifiedOtp()) {
            $user->update(['email_verified_at' => now()]);
        }

        return true;
    }

    public function resend(User $user, OtpPurpose $purpose = OtpPurpose::Registration): OtpCode
    {
        return $this->generateAndSend($user, $purpose);
    }

    private function latestPending(User $user, OtpPurpose $purpose): ?OtpCode
    {
        return $user->otpCodes()
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();
    }

    private function invalidatePending(User $user, OtpPurpose $purpose): void
    {
        $user->otpCodes()
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);
    }
}
