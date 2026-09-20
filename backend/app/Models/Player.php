<?php

namespace App\Models;

use App\Enums\PlayerRole;
use Database\Factories\PlayerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A player IS a registered user's cricket profile — there is no
 * player creation independent of an account (one-to-one with User).
 */
#[Fillable(['user_id', 'date_of_birth', 'role', 'batting_style', 'bowling_style'])]
class Player extends Model
{
    /** @use HasFactory<PlayerFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'role' => PlayerRole::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Team, $this>
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_player')
            ->withPivot(['is_captain', 'is_vice_captain', 'is_wicket_keeper'])
            ->withTimestamps();
    }
}
