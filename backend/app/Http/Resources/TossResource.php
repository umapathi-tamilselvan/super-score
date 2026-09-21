<?php

namespace App\Http\Resources;

use App\Models\Toss;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Toss */
class TossResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'winner' => new TeamResource($this->winner),
            'decision' => $this->decision->value,
            'batting_team' => new TeamResource($this->battingTeam()),
            'bowling_team' => new TeamResource($this->bowlingTeam()),
        ];
    }
}
