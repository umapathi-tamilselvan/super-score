<?php

namespace App\Models;

use Database\Factories\PlayingXiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['match_id', 'team_id', 'player_id', 'is_captain', 'is_vice_captain', 'is_wicket_keeper', 'is_substitute'])]
class PlayingXi extends Model
{
    /** @use HasFactory<PlayingXiFactory> */
    use HasFactory;

    protected $table = 'playing_xi';

    protected function casts(): array
    {
        return [
            'is_captain' => 'boolean',
            'is_vice_captain' => 'boolean',
            'is_wicket_keeper' => 'boolean',
            'is_substitute' => 'boolean',
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

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
