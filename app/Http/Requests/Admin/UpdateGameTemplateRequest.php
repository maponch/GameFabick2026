<?php

namespace App\Http\Requests\Admin;

use App\Models\GameTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\Admin\Concerns\RefusesWhenPublished;

class UpdateGameTemplateRequest extends FormRequest
{
    use RefusesWhenPublished; 

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
            'format_ids'             => ['sometimes', 'array', 'min:1'],
            'format_ids.*'           => ['integer', 'exists:game_formats,id'],
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
            $this->refuseIfTemplatePublished($validator);
            $template = $this->route('template');
            if (!$template instanceof \App\Models\GameTemplate) {
                $template = \App\Models\GameTemplate::find($template);
            }
            if (!$template) {
                return;
            }

            // Si le template est en mode prédéfini, refuser toute modification du card_schema
            if ($template->card_layout && $this->has('card_schema')) {
                $validator->errors()->add(
                    'card_schema',
                    'Le schéma des cartes ne peut pas être modifié pour un template en mode prédéfini.'
                );
            }

            // Le card_layout est figé à la création, refuser toute modification
            if ($this->has('card_layout') && $this->input('card_layout') !== $template->card_layout) {
                $validator->errors()->add(
                    'card_layout',
                    'Le mode du template (libre ou prédéfini) ne peut pas être modifié après création.'
                );
            }
        });
    }
}