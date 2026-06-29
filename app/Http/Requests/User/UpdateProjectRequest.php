<?php

namespace App\Http\Requests\User;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        if (!$project instanceof Project) {
            $project = Project::find($project);
        }
        return $project && $project->user_id === $this->user()?->id;
    }

    public function rules(): array
    {
        return [
            'name'         => ['sometimes', 'required', 'string', 'max:191'],
            'type_id'      => ['sometimes', 'required', 'integer', 'exists:types,id'],
            'card_layout'  => ['sometimes', 'nullable', 'string', 'exists:card_layouts,slug'],
            'description'  => ['sometimes', 'nullable', 'string'],
            'rules'        => ['sometimes', 'nullable', 'string'],
            'mode'         => ['sometimes', 'required', 'in:printable,existing_deck'],
            'card_schema'  => ['sometimes', 'nullable', 'array'],
            'min_players'  => ['sometimes', 'required', 'integer', 'min:1'],
            'max_players'  => ['sometimes', 'required', 'integer', 'min:1'],
            'duration_min' => ['sometimes', 'required', 'integer', 'min:1'],
            'duration_max' => ['sometimes', 'required', 'integer', 'min:1'],
            'format_ids'   => ['sometimes', 'array'],
            'format_ids.*' => ['integer', 'exists:game_formats,id'],
            'allow_duplication' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $min = $this->input('min_players');
            $max = $this->input('max_players');
            if ($min !== null && $max !== null && $max < $min) {
                $validator->errors()->add('max_players', 'Doit être ≥ joueurs min.');
            }

            $dMin = $this->input('duration_min');
            $dMax = $this->input('duration_max');
            if ($dMin !== null && $dMax !== null && $dMax < $dMin) {
                $validator->errors()->add('duration_max', 'Doit être ≥ durée min.');
            }
        });
    }
}