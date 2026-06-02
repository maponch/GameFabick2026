<?php

namespace App\Http\Requests\Admin\Reference;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->isAdmin();
    }

    public function rules(): array
    {
        $typeId = $this->route('type')?->id ?? $this->route('type');

        return [
            'name' => ['required', 'string', 'max:80', Rule::unique('types', 'name')->ignore($typeId)->whereNull('deleted_at')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Un type avec ce nom existe déjà.',
        ];
    }
}