<?php

namespace App\Http\Resources;

use App\DataTransferObjects\InningsProjection;
use App\Models\Innings;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Wraps an Innings model together with its (already-computed)
 * InningsProjection — pass `['innings' => $innings, 'projection' =>
 * $projection]` as the resource. Keeping projection computation in the
 * controller/service layer, not here, keeps this a pure presenter.
 */
class InningsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Innings $innings */
        $innings = $this->resource['innings'];
        /** @var InningsProjection $projection */
        $projection = $this->resource['projection'];

        return [
            'id' => $innings->id,
            'innings_number' => $innings->innings_number,
            'team' => new TeamResource($innings->team),
            'status' => $innings->status->value,
            'target' => $innings->target,
            'current_striker' => $innings->currentStriker ? new PlayerResource($innings->currentStriker) : null,
            'current_non_striker' => $innings->currentNonStriker ? new PlayerResource($innings->currentNonStriker) : null,
            'current_bowler' => $innings->currentBowler ? new PlayerResource($innings->currentBowler) : null,
            'score' => [
                'runs' => $projection->teamRuns,
                'wickets' => $projection->wickets,
                'overs' => $projection->oversDisplay(),
                'run_rate' => $projection->runRate(),
            ],
            'required' => $innings->target ? [
                'runs' => max(0, $innings->target - $projection->teamRuns),
                'balls_left' => max(0, ($innings->match->overs * 6) - $projection->legalBallsBowled),
            ] : null,
            'batter_stats' => array_map(fn ($stat) => [
                'player' => new PlayerResource($stat->player),
                'runs' => $stat->runs,
                'balls' => $stat->balls,
                'fours' => $stat->fours,
                'sixes' => $stat->sixes,
                'strike_rate' => $stat->strikeRate(),
                'is_out' => $stat->isOut,
                'dismissal' => $stat->dismissalDescription,
            ], array_values($projection->batterStats)),
            'bowler_stats' => array_map(fn ($stat) => [
                'player' => new PlayerResource($stat->player),
                'overs' => $stat->oversDisplay(),
                'runs_conceded' => $stat->runsConceded,
                'wickets' => $stat->wickets,
                'economy' => $stat->economy(),
            ], array_values($projection->bowlerStats)),
            'extras' => $projection->extras,
            'fall_of_wickets' => array_map(fn ($fow) => [
                'wicket_number' => $fow->wicketNumber,
                'team_score' => $fow->teamScoreAtFall,
                'player' => new PlayerResource($fow->playerOut),
                'overs' => $fow->oversDisplay,
            ], $projection->fallOfWickets),
        ];
    }
}
