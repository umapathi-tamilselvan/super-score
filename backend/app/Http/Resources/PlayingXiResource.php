<?php

namespace App\Http\Resources;

use App\Models\PlayingXi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PlayingXi */
class PlayingXiResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'player' => new PlayerResource($this->whenLoaded('player')),
            'is_captain' => $this->is_captain,
            'is_vice_captain' => $this->is_vice_captain,
            'is_wicket_keeper' => $this->is_wicket_keeper,
            'is_substitute' => $this->is_substitute,
        ];
    }
}
