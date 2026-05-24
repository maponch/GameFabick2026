<?php

namespace App\Http\Requests\Admin;

use App\Models\GameTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGameTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->isAdmin();
    }

    public function rules(): array
    {
        $templateId = $this->route('template')?->id ?? $this->route('template');

        return [
            'name'                   => ['sometimes', 'required', 'string', 'max:120'],
            'slug'                   => ['sometimes', 'nullable', 'string', 'max:140', 'regex:/^[a-z0-9-]+$/', Rule::unique('game_templates', 'slug')->ignore($templateId)],
            'description'            => ['sometimes', 'nullable', 'string', 'max:2000'],
            'rules'                  => ['sometimes', 'nullable', 'string'],
            'type_id'                => ['sometimes', 'required', 'integer', 'exists:types,id'],
            'min_players'            => ['sometimes', 'required', 'integer', 'min:1', 'max:99'],
            'max_players'            => ['sometimes', 'required', 'integer', 'min:1', 'max:99', 'gte:min_players'],
            'duration_min'           => ['sometimes', 'required', 'integer', 'min:1', 'max:600'],
            'duration_max'           => ['sometimes', 'required', 'integer', 'min:1', 'max:600', 'gte:duration_min'],
            'supports_existing_deck' => ['sometimes', 'required', 'boolean'],
            'status'                 => ['sometimes', 'required', Rule::in(GameTemplate::STATUSES)],

            'card_schema'                => ['sometimes', 'nullable', 'array'],
            'card_schema.*.key'          => ['required_with:card_schema', 'string', 'max:50', 'regex:/^[a-z][a-z0-9_]*$/'],
            'card_schema.*.label'        => ['required_with:card_schema', 'string', 'max:100'],
            'card_schema.*.type'         => ['required_with:card_schema', Rule::in(['text', 'textarea', 'number', 'select', 'boolean'])],
            'card_schema.*.required'     => ['nullable', 'boolean'],
            'card_schema.*.options'      => ['nullable', 'array'],
            'card_schema.*.options.*'    => ['string', 'max:100'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->has('card_schema')) return;
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