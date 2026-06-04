<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreGameTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name'                   => ['required', 'string', 'max:120'],
            'description'            => ['nullable', 'string', 'max:2000'],
            'rules'                  => ['nullable', 'string'],
            'type_id'                => ['required', 'integer', 'exists:types,id'],
            'min_players'            => ['required', 'integer', 'min:1', 'max:99'],
            'max_players'            => ['required', 'integer', 'min:1', 'max:99', 'gte:min_players'],
            'duration_min'           => ['required', 'integer', 'min:1', 'max:600'],
            'duration_max'           => ['required', 'integer', 'min:1', 'max:600', 'gte:duration_min'],
            'format_ids'             => ['required', 'array', 'min:1'],
            'format_ids.*'           => ['integer', 'exists:game_formats,id'],
            'card_layout'            => ['nullable', 'string', 'exists:card_layouts,slug'],
        ];
    }
}