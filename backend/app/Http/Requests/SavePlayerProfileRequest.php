<?php

namespace App\Http\Requests;

use App\Enums\PlayerRole;
use App\Models\Player;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The single form for a user's own player profile — creates it the
 * first time, updates it every time after. There is no separate
 * "create a player for someone else" request.
 */
class SavePlayerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $player = $this->user()->player;

        return $player
            ? $this->user()->can('update', $player)
            : $this->user()->can('create', Player::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'role' => ['required', Rule::enum(PlayerRole::class)],
            'batting_style' => ['nullable', 'string', 'max:255'],
            'bowling_style' => ['nullable', 'string', 'max:255'],
        ];
    }
}
