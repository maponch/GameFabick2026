<?php

namespace App\Http\Requests\Admin\Reference;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGameFormatRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->isAdmin();
    }

    public function rules(): array
    {
        $formatId = $this->route('format')?->id ?? $this->route('format');

        return [
            'name' => ['required', 'string', 'max:80', Rule::unique('game_formats', 'name')->ignore($formatId)->whereNull('deleted_at')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Un format avec ce nom existe déjà.',
        ];
    }
}