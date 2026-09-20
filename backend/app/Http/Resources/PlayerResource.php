<?php

namespace App\Http\Resources;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Player */
class PlayerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->user->name,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'photo_url' => $this->user->profile_photo_path
                ? Storage::disk('public')->url($this->user->profile_photo_path)
                : null,
            'role' => $this->role->value,
            'batting_style' => $this->batting_style,
            'bowling_style' => $this->bowling_style,
            'is_captain' => $this->whenPivotLoaded('team_player', fn () => (bool) $this->pivot->is_captain),
            'is_vice_captain' => $this->whenPivotLoaded('team_player', fn () => (bool) $this->pivot->is_vice_captain),
            'is_wicket_keeper' => $this->whenPivotLoaded('team_player', fn () => (bool) $this->pivot->is_wicket_keeper),
            'created_at' => $this->created_at,
        ];
    }
}
