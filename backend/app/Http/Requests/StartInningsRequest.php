<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StartInningsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('match'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $match = $this->route('match');
        $teamIds = $match->teams->pluck('id');
        $battingTeamId = $this->input('team_id');
        $bowlingTeamId = $teamIds->first(fn ($id) => $id != $battingTeamId);

        return [
            'team_id' => ['required', Rule::in($teamIds)],
            'striker_player_id' => [
                'required', 'different:non_striker_player_id',
                Rule::exists('playing_xi', 'player_id')->where('match_id', $match->id)->where('team_id', $battingTeamId),
            ],
            'non_striker_player_id' => [
                'required',
                Rule::exists('playing_xi', 'player_id')->where('match_id', $match->id)->where('team_id', $battingTeamId),
            ],
            'opening_bowler_player_id' => [
                'required',
                Rule::exists('playing_xi', 'player_id')->where('match_id', $match->id)->where('team_id', $bowlingTeamId),
            ],
        ];
    }
}
