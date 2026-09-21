<?php

namespace App\Http\Requests;

use App\Enums\TossDecision;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordTossRequest extends FormRequest
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

        return [
            'winner_team_id' => ['required', 'integer', Rule::in($match->teams->pluck('id'))],
            'decision' => ['required', Rule::enum(TossDecision::class)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->route('match')->hasBothPlayingXiComplete()) {
                $validator->errors()->add('winner_team_id', 'Both teams must have a complete playing XI before the toss.');
            }
        });
    }
}
