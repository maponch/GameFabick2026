<?php

namespace App\Http\Requests\Admin;

use App\Models\GameTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeGameTemplateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->isAdmin();
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(GameTemplate::STATUSES)],
        ];
    }
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $status = $this->input('status');
            if ($status !== 'published') {
                return;
            }

            $template = $this->route('template');
            if (!$template instanceof \App\Models\GameTemplate) {
                $template = \App\Models\GameTemplate::find($template);
            }
            if (!$template) {
                return;
            }

            $report = $template->publishabilityReport();
            if (!$report['ready']) {
                $validator->errors()->add(
                    'status',
                    'Le template ne peut pas être publié. Manque : ' . implode(' ; ', $report['missing'])
                );
            }
        });
    }
}