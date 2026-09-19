<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\PreferredRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'mobile_number', 'preferred_role', 'profile_photo_path', 'profile_completed_at', 'password', 'email_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'profile_completed_at' => 'datetime',
            'preferred_role' => PreferredRole::class,
            'password' => 'hashed',
        ];
    }

    /**
     * @return HasMany<OtpCode, $this>
     */
    public function otpCodes(): HasMany
    {
        return $this->hasMany(OtpCode::class);
    }

    public function hasVerifiedOtp(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function hasCompletedProfile(): bool
    {
        return $this->profile_completed_at !== null;
    }
}
