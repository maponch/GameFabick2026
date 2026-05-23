<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreObjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:120'],
            'description'           => ['nullable', 'string', 'max:1000'],
            'quantity'              => ['required', 'integer', 'min:1', 'max:999'],
            'default_color'         => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'default_image_path'    => ['nullable', 'string', 'max:255'],
            'existing_deck_mapping' => ['nullable', 'array'],
            'existing_deck_mapping.*' => ['string', 'max:10'],
            'custom_data'           => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'default_color.regex' => 'La couleur doit être au format hexadécimal (#RRGGBB).',
        ];
    }
}