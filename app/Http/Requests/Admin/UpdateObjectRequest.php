<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesCustomData;
use App\Http\Requests\Admin\Concerns\ValidatesDeckMapping;
use Illuminate\Foundation\Http\FormRequest;

class UpdateObjectRequest extends FormRequest
{
    use ValidatesCustomData;
    use ValidatesDeckMapping;

    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name'                  => ['sometimes', 'required', 'string', 'max:120'],
            'description'           => ['sometimes', 'nullable', 'string', 'max:1000'],
            'quantity'              => ['sometimes', 'required', 'integer', 'min:1', 'max:999'],
            'default_color'         => ['sometimes', 'nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'default_image_path'    => ['sometimes', 'nullable', 'string', 'max:255'],
            'existing_deck_mapping' => ['sometimes', 'nullable', 'array'],
            'existing_deck_mapping.*' => ['string', 'max:20'],
            'custom_data'           => ['sometimes', 'nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'default_color.regex' => 'La couleur doit être au format hexadécimal (#RRGGBB).',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateCustomData($validator);
            $this->validateDeckMapping($validator);
        });
    }
}