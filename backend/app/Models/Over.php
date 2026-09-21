<?php

namespace App\Models;

use Database\Factories\OverFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['over_number', 'bowler_id'])]
class Over extends Model
{
    /** @use HasFactory<OverFactory> */
    use HasFactory;

    public function innings(): BelongsTo
    {
        return $this->belongsTo(Innings::class);
    }

    public function bowler(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * @return HasMany<Delivery, $this>
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function legalBallCount(): int
    {
        return $this->deliveries()->where('is_legal_delivery', true)->count();
    }

    public function isComplete(): bool
    {
        return $this->legalBallCount() >= 6;
    }
}
