<?php

namespace App\Http\Resources;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A team together with its full squad, including per-player leadership
 * roles (captain/vice-captain/wicket-keeper) via PlayerResource's pivot
 * fields.
 *
 * @mixin Team
 */
class TeamSquadResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge((new TeamResource($this->resource))->toArray($request), [
            'players' => PlayerResource::collection($this->whenLoaded('players')),
        ]);
    }
}
