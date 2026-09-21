<?php

namespace App\Http\Resources;

use App\Models\CricketMatch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CricketMatch */
class MatchResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'format' => $this->format->value,
            'overs' => $this->overs,
            'date' => $this->date->toDateString(),
            'time' => $this->time,
            'venue' => $this->venue,
            'status' => $this->status->value,
            'teams' => $this->whenLoaded('teams', fn () => $this->teams->map(fn ($team) => [
                'id' => $team->id,
                'name' => $team->name,
                'short_name' => $team->short_name,
                'side' => $team->pivot->side,
            ])),
            'has_both_teams_selected' => $this->hasBothTeamsSelected(),
            'has_both_playing_xi_complete' => $this->hasBothTeamsSelected() && $this->hasBothPlayingXiComplete(),
            'toss' => $this->whenLoaded('toss', fn () => $this->toss ? new TossResource($this->toss) : null),
            'created_at' => $this->created_at,
        ];
    }
}
