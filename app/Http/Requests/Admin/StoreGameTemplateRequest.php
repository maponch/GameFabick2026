<?php

namespace App\Http\Requests\Admin;

use App\Models\GameTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGameTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin ?? false;
    }

    public function rules(): array
    {
        return [
            'name'                   => ['required', 'string', 'max:120'],
            'slug'                   => ['nullable', 'string', 'max:140', 'regex:/^[a-z0-9-]+$/', 'unique:game_templates,slug'],
            'description'            => ['nullable', 'string', 'max:2000'],
            'rules'                  => ['nullable', 'string'],
            'type_id'                => ['required', 'integer', 'exists:types,id'],
            'min_players'            => ['required', 'integer', 'min:1', 'max:99'],
            'max_players'            => ['required', 'integer', 'min:1', 'max:99', 'gte:min_players'],
            'duration_min'           => ['required', 'integer', 'min:1', 'max:600'],
            'duration_max'           => ['required', 'integer', 'min:1', 'max:600', 'gte:duration_min'],
            'supports_existing_deck' => ['required', 'boolean'],
            'status'                 => ['required', Rule::in(GameTemplate::STATUSES)],

            'card_schema'                => ['nullable', 'array'],
            'card_schema.*.key'          => ['required_with:card_schema', 'string', 'max:50', 'regex:/^[a-z][a-z0-9_]*$/'],
            'card_schema.*.label'        => ['required_with:card_schema', 'string', 'max:100'],
            'card_schema.*.type'         => ['required_with:card_schema', Rule::in(['text', 'textarea', 'number', 'select', 'boolean'])],
            'card_schema.*.required'     => ['nullable', 'boolean'],
            'card_schema.*.options'      => ['nullable', 'array'],
            'card_schema.*.options.*'    => ['string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'card_schema.*.key.regex'   => 'La clé du champ doit commencer par une lettre minuscule et ne contenir que des lettres minuscules, chiffres et underscores.',
            'card_schema.*.type.in'     => 'Le type de champ doit être text, textarea, number, select ou boolean.',
            'slug.regex'                => 'Le slug ne peut contenir que des lettres minuscules, chiffres et tirets.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $schema = $this->input('card_schema', []);
            if (!is_array($schema)) return;

            $keys = [];
            foreach ($schema as $i => $field) {
                if (!isset($field['key'])) continue;
                if (in_array($field['key'], $keys, true)) {
                    $validator->errors()->add("card_schema.$i.key", "La clé '{$field['key']}' est dupliquée.");
                }
                $keys[] = $field['key'];

                if (($field['type'] ?? null) === 'select' && empty($field['options'])) {
                    $validator->errors()->add("card_schema.$i.options", "Un champ de type 'select' doit avoir au moins une option.");
                }
            }
        });
    }
}