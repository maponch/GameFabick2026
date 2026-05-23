<?php

namespace App\Http\Requests\Admin;

use App\Models\GameTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeGameTemplateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(GameTemplate::STATUSES)],
        ];
    }
}