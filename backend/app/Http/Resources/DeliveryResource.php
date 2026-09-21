<?php

namespace App\Http\Resources;

use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Delivery */
class DeliveryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'over_number' => $this->over->over_number,
            'sequence_in_innings' => $this->sequence_in_innings,
            'striker' => $this->striker->user->name,
            'bowler' => $this->bowler->user->name,
            'runs_off_bat' => $this->runs_off_bat,
            'extra_type' => $this->extra_type?->value,
            'extra_runs' => $this->extra_runs,
            'total_runs' => $this->totalRuns(),
            'is_wicket' => $this->is_wicket,
            'dismissal_type' => $this->dismissal_type?->value,
            'dismissed_player' => $this->dismissedPlayer?->user->name,
            'is_legal_delivery' => $this->is_legal_delivery,
            'is_free_hit' => $this->is_free_hit,
            'label' => $this->resource->label(),
        ];
    }
}
