<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateObjectRequest extends FormRequest
{
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
            'existing_deck_mapping.*' => ['string', 'max:10'],
            'custom_data'           => ['sometimes', 'nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'default_color.regex' => 'La couleur doit être au format hexadécimal (#RRGGBB).',
        ];
    }
}