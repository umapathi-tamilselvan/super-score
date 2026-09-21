<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StartNextOverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('innings')->match);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $innings = $this->route('innings');
        $bowlingTeamId = $innings->match->teams->pluck('id')->first(fn ($id) => $id != $innings->team_id);

        return [
            'bowler_id' => [
                'required', 'integer',
                Rule::exists('playing_xi', 'player_id')->where('match_id', $innings->match_id)->where('team_id', $bowlingTeamId),
            ],
        ];
    }
}
