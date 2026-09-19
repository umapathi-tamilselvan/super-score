<?php

namespace App\Http\Requests;

use App\Enums\PreferredRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'mobile_number' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('users', 'mobile_number')->ignore($userId)],
            'preferred_role' => ['sometimes', 'required', Rule::enum(PreferredRole::class)],
            'photo' => ['sometimes', 'nullable', 'image', 'max:4096'],
        ];
    }
}
