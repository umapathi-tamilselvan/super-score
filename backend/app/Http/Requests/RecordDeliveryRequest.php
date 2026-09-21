<?php

namespace App\Http\Requests;

use App\DataTransferObjects\DeliveryInput;
use App\Enums\DismissalType;
use App\Enums\ExtraType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordDeliveryRequest extends FormRequest
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
        return [
            'runs_off_bat' => ['sometimes', 'integer', 'min:0', 'max:6'],
            'extra_type' => ['nullable', Rule::enum(ExtraType::class)],
            'extra_runs' => ['sometimes', 'integer', 'min:0', 'max:6'],
            'is_wicket' => ['sometimes', 'boolean'],
            'dismissal_type' => ['required_if:is_wicket,true', Rule::enum(DismissalType::class)],
            'dismissed_player_id' => ['required_if:is_wicket,true', 'integer'],
            'fielder_id' => ['nullable', 'integer'],
        ];
    }

    public function toDeliveryInput(): DeliveryInput
    {
        $data = $this->validated();

        return new DeliveryInput(
            runsOffBat: $data['runs_off_bat'] ?? 0,
            extraType: isset($data['extra_type']) ? ExtraType::from($data['extra_type']) : null,
            extraRuns: $data['extra_runs'] ?? 0,
            isWicket: $data['is_wicket'] ?? false,
            dismissalType: isset($data['dismissal_type']) ? DismissalType::from($data['dismissal_type']) : null,
            dismissedPlayerId: $data['dismissed_player_id'] ?? null,
            fielderId: $data['fielder_id'] ?? null,
        );
    }
}
