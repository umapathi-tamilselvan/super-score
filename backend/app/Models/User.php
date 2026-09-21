<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\PreferredRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    /**
     * @return HasMany<Team, $this>
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    /**
     * A user's own cricket player profile — at most one, created only by
     * the user themselves (see PlayerPolicy).
     *
     * @return HasOne<Player, $this>
     */
    public function player(): HasOne
    {
        return $this->hasOne(Player::class);
    }

    /**
     * Matches this user organizes (creates and drives through setup).
     *
     * @return HasMany<CricketMatch, $this>
     */
    public function organizedMatches(): HasMany
    {
        return $this->hasMany(CricketMatch::class);
    }

    public function hasVerifiedOtp(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function hasCompletedProfile(): bool
    {
        return $this->profile_completed_at !== null;
    }

    public function isPlayer(): bool
    {
        return $this->player !== null;
    }
}
