<?php

namespace App\Http\Requests\Admin\Reference;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGameFormatRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80', Rule::unique('game_formats', 'name')->whereNull('deleted_at')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Un format avec ce nom existe déjà.',
        ];
    }
}