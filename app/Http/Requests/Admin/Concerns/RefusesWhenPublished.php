<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Models\GameTemplate;
use Illuminate\Validation\Validator;

trait RefusesWhenPublished
{
    protected function refuseIfTemplatePublished(Validator $validator): void
    {
        $template = $this->route('template');
        if (!$template instanceof GameTemplate) {
            $template = GameTemplate::find($template);
        }
        if (!$template) {
            return;
        }

        if ($template->status === GameTemplate::STATUS_PUBLISHED) {
            $validator->errors()->add(
                'status',
                'Ce template est publié. Repassez-le en brouillon pour le modifier.'
            );
        }
    }
}