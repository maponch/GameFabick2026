<?php

namespace App\Http\Requests\User;

use App\Models\Comment;
use App\Models\ModerationAction;
use App\Models\Project;
use App\Models\Report;
use App\Models\GameTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreReportRequest extends FormRequest
{
    private const ALLOWED_TYPES = [
        'project' => Project::class,
        'comment' => Comment::class,
        'template' => GameTemplate::class,
    ];

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'reportable_type' => ['required', 'string', Rule::in(array_keys(self::ALLOWED_TYPES))],
            'reportable_id'   => ['required', 'integer', 'min:1'],
            'reason_code'     => ['required', Rule::in(array_keys(ModerationAction::REASON_LABELS))],
            'reason_text'     => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $type = $this->input('reportable_type');
            $id   = (int) $this->input('reportable_id');
            $modelClass = self::ALLOWED_TYPES[$type];

            $target = $modelClass::find($id);
            if (! $target) {
                $v->errors()->add('reportable_id', 'La cible du signalement est introuvable.');
                return;
            }

            $reporterId = $this->user()->id;

            $targetUserId = match ($type) {
                'project' => $target->user_id,
                'comment' => $target->user_id,
                default   => null, // template = pas d'auteur user
            };

            if ($targetUserId !== null && $targetUserId === $reporterId) {
                $v->errors()->add('reportable_id', 'Vous ne pouvez pas signaler votre propre contenu.');
                return;
            }

            if ($this->input('reason_code') === ModerationAction::REASON_OTHER
                && trim((string) $this->input('reason_text')) === '') {
                $v->errors()->add('reason_text', 'Une précision est requise pour le motif "Autre".');
            }

            $exists = Report::where('reporter_id', $reporterId)
                ->where('reportable_type', $modelClass)
                ->where('reportable_id', $id)
                ->where('status', Report::STATUS_PENDING)
                ->exists();

            if ($exists) {
                $v->errors()->add('reportable_id', 'Vous avez déjà un signalement en cours sur cet élément.');
            }
                    });
                }

    public function resolvedReportableClass(): string
    {
        return self::ALLOWED_TYPES[$this->input('reportable_type')];
    }
}