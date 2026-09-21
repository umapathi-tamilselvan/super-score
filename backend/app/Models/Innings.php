<?php

namespace App\Models;

use App\Enums\InningsStatus;
use Database\Factories\InningsFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['team_id', 'innings_number', 'target', 'status', 'current_striker_id', 'current_non_striker_id', 'current_bowler_id'])]
class Innings extends Model
{
    /** @use HasFactory<InningsFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => InningsStatus::class,
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(CricketMatch::class, 'match_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function currentStriker(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'current_striker_id');
    }

    public function currentNonStriker(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'current_non_striker_id');
    }

    public function currentBowler(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'current_bowler_id');
    }

    /**
     * @return HasMany<Over, $this>
     */
    public function overs(): HasMany
    {
        return $this->hasMany(Over::class);
    }

    /**
     * @return HasMany<Delivery, $this>
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    public function currentOver(): ?Over
    {
        return $this->overs()->latest('over_number')->first();
    }

    public function isInProgress(): bool
    {
        return $this->status === InningsStatus::InProgress;
    }
}
