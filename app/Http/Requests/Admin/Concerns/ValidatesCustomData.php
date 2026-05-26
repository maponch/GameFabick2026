<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Models\GameTemplate;
use Illuminate\Validation\Validator;

trait ValidatesCustomData
{
    protected function validateCustomData(Validator $validator): void
    {
        $template = $this->route('template');
        if (!$template instanceof GameTemplate) {
            $template = GameTemplate::find($template);
        }
        if (!$template) {
            return;
        }

        $schema = $template->card_schema ?? [];
        $custom = $this->input('custom_data');

        if ($custom === null) {
            $custom = [];
        }

        if (!is_array($custom)) {
            $validator->errors()->add('custom_data', 'Le format des champs personnalisés est invalide.');
            return;
        }

        $allowedKeys = array_column($schema, 'key');

        foreach (array_keys($custom) as $key) {
            if (!in_array($key, $allowedKeys, true)) {
                $validator->errors()->add("custom_data.$key", "Le champ « $key » n'est pas défini dans ce template.");
            }
        }

        foreach ($schema as $field) {
            $key = $field['key'];
            $label = $field['label'] ?? $key;
            $type = $field['type'] ?? 'text';
            $required = !empty($field['required']);
            $value = $custom[$key] ?? null;
            $empty = $value === null || $value === '';

            if ($required && $empty && $type !== 'boolean') {
                $validator->errors()->add("custom_data.$key", "Le champ « $label » est requis.");
                continue;
            }

            if ($empty) {
                continue;
            }

            switch ($type) {
                case 'number':
                    if (!is_numeric($value)) {
                        $validator->errors()->add("custom_data.$key", "Le champ « $label » doit être un nombre.");
                    }
                    break;

                case 'boolean':
                    if (!is_bool($value)) {
                        $validator->errors()->add("custom_data.$key", "Le champ « $label » doit être vrai ou faux.");
                    }
                    break;

                case 'select':
                    $options = $field['options'] ?? [];
                    if (!in_array($value, $options, true)) {
                        $validator->errors()->add("custom_data.$key", "La valeur du champ « $label » n'est pas une option valide.");
                    }
                    break;

                case 'text':
                case 'textarea':
                    if (!is_string($value)) {
                        $validator->errors()->add("custom_data.$key", "Le champ « $label » doit être du texte.");
                    }
                    break;
            }
        }
    }
}