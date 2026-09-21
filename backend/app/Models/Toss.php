<?php

namespace App\Models;

use App\Enums\TossDecision;
use Database\Factories\TossFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['match_id', 'winner_team_id', 'decision'])]
class Toss extends Model
{
    /** @use HasFactory<TossFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'decision' => TossDecision::class,
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(CricketMatch::class, 'match_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }

    public function battingTeam(): Team
    {
        return $this->decision === TossDecision::Bat
            ? $this->winner
            : $this->match->teams->firstWhere('id', '!=', $this->winner_team_id);
    }

    public function bowlingTeam(): Team
    {
        return $this->match->teams->firstWhere('id', '!=', $this->battingTeam()->id);
    }
}
