<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SelectPlayingXiRequest extends FormRequest
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
        return [
            'player_ids' => ['required', 'array', 'size:'.config('cricket.playing_xi_size')],
            'player_ids.*' => ['required', 'integer', 'distinct', Rule::exists('players', 'id')],
            'captain_player_id' => ['required', 'integer', Rule::in($this->input('player_ids', []))],
            'wicket_keeper_player_id' => ['required', 'integer', Rule::in($this->input('player_ids', []))],
            'vice_captain_player_id' => ['nullable', 'integer', Rule::in($this->input('player_ids', []))],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->route('match')->hasBothTeamsSelected()) {
                $validator->errors()->add('team', 'Both teams must be selected before choosing a playing XI.');
            }
        });
    }
}
