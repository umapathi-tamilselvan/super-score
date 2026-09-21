<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SelectTeamsRequest extends FormRequest
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
            'team_a_id' => ['required', 'integer', Rule::exists('teams', 'id'), 'different:team_b_id'],
            'team_b_id' => ['required', 'integer', Rule::exists('teams', 'id')],
        ];
    }
}
