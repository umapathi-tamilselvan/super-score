<?php

namespace App\Http\Requests;

use App\Enums\MatchFormat;
use App\Models\CricketMatch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', CricketMatch::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'format' => ['required', Rule::enum(MatchFormat::class)],
            'overs' => ['required', 'integer', 'min:1', 'max:50'],
            'date' => ['required', 'date'],
            'time' => ['nullable', 'date_format:H:i'],
            'venue' => ['nullable', 'string', 'max:255'],
        ];
    }
}
