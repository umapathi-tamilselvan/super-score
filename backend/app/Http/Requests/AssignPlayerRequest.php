<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignPlayerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('team'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $team = $this->route('team');

        return [
            'player_id' => [
                'required',
                Rule::exists('players', 'id'),
                Rule::unique('team_player', 'player_id')->where('team_id', $team->id),
            ],
        ];
    }
}
