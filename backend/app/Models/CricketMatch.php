<?php

namespace App\Models;

use App\Enums\MatchFormat;
use App\Enums\MatchSide;
use App\Enums\MatchStatus;
use Database\Factories\CricketMatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * The `matches` table — named CricketMatch because `Match` is a
 * reserved word in PHP.
 */
#[Fillable(['name', 'format', 'overs', 'date', 'time', 'venue', 'status'])]
class CricketMatch extends Model
{
    /** @use HasFactory<CricketMatchFactory> */
    use HasFactory;

    protected $table = 'matches';

    protected function casts(): array
    {
        return [
            'format' => MatchFormat::class,
            'status' => MatchStatus::class,
            'date' => 'date',
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
        return $this->belongsToMany(Team::class, 'match_teams', 'match_id', 'team_id')
            ->withPivot(['side'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<PlayingXi, $this>
     */
    public function playingXi(): HasMany
    {
        return $this->hasMany(PlayingXi::class, 'match_id');
    }

    public function toss(): HasOne
    {
        return $this->hasOne(Toss::class, 'match_id');
    }

    /**
     * @return HasMany<Innings, $this>
     */
    public function innings(): HasMany
    {
        return $this->hasMany(Innings::class, 'match_id');
    }

    public function teamForSide(MatchSide $side): ?Team
    {
        return $this->teams->firstWhere('pivot.side', $side->value);
    }

    public function sideForTeam(Team $team): ?MatchSide
    {
        $pivotSide = $this->teams->firstWhere('id', $team->id)?->pivot->side;

        return $pivotSide ? MatchSide::from($pivotSide) : null;
    }

    public function hasBothTeamsSelected(): bool
    {
        return $this->teams()->count() === 2;
    }

    public function hasCompletePlayingXiFor(Team $team): bool
    {
        return $this->playingXi()
            ->where('team_id', $team->id)
            ->where('is_substitute', false)
            ->count() === config('cricket.playing_xi_size');
    }

    public function hasBothPlayingXiComplete(): bool
    {
        return $this->hasBothTeamsSelected()
            && $this->teams->every(fn (Team $team) => $this->hasCompletePlayingXiFor($team));
    }
}
