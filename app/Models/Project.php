<?php

namespace App\Models;

use App\Models\GameFormat;
use App\Models\ProjectObject;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\Publishable;
use App\Models\Concerns\Reportable;

class Project extends Model
{
    use Publishable;
    use Reportable;

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED  = 'archived';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PUBLISHED,
        self::STATUS_ARCHIVED,
    ];

    protected $fillable = [
        'user_id',
        'type_id',
        'template_id',
        'name',
        'description',
        'rules',
        'card_schema',
        'card_layout',
        'mode',
        'duration_min',
        'duration_max',
        'min_players',
        'max_players',
        'status',
        'allow_duplication',
        'based_on_project_id',
    ];

    protected $casts = [
        'card_schema' => 'array',
        'allow_duplication' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function template()
    {
        return $this->belongsTo(GameTemplate::class, 'template_id');
    }

    public function objects()
    {
        return $this->hasMany(ProjectObject::class);
    }
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function playHistory()
    {
        return $this->hasMany(PlayHistory::class);
    }
    public function formats()
    {
        return $this->belongsToMany(GameFormat::class, 'project_format')
                    ->withTimestamps()
                    ->withTrashed();
    }
    public function moderationActions()
    {
        return $this->hasMany(ModerationAction::class);
    }
    public function basedOnProject()
    {
        return $this->belongsTo(Project::class, 'based_on_project_id');
    }

    public function derivedProjects()
    {
        return $this->hasMany(Project::class, 'based_on_project_id');
    }
    public function toSnapshot(): array
    {
        $this->loadMissing(['formats', 'objects', 'type', 'template']);

        return [
            'id'                => $this->id,
            'name'               => $this->name,
            'description'        => $this->description,
            'rules'              => $this->rules,
            'mode'               => $this->mode,
            'status'             => $this->status,
            'type'               => $this->type?->name,
            'type_id'            => $this->type_id,
            'template_id'        => $this->template_id,
            'based_on_project_id'=> $this->based_on_project_id,
            'card_schema'        => $this->card_schema,
            'card_layout'        => $this->card_layout,
            'min_players'        => $this->min_players,
            'max_players'        => $this->max_players,
            'duration_min'       => $this->duration_min,
            'duration_max'       => $this->duration_max,
            'allow_duplication'  => (bool) $this->allow_duplication,
            'formats'            => $this->formats->map(fn ($f) => [
                'id'   => $f->id,
                'slug' => $f->slug,
                'name' => $f->name,
            ])->toArray(),
            'objects'            => $this->objects->map(fn ($o) => [
                'id'                    => $o->id,
                'name'                  => $o->name,
                'description'           => $o->description,
                'quantity'              => $o->quantity,
                'default_color'         => $o->default_color,
                'default_image_path'    => $o->default_image_path,
                'existing_deck_mapping' => $o->existing_deck_mapping,
                'custom_data'           => $o->custom_data,
            ])->toArray(),
            'snapshotted_at'     => now()->toIso8601String(),
        ];
    }
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function averageRating(): float
    {
        return round($this->ratings()->avg('score') ?? 0, 1);
    }

    public function ratingsCount(): int
    {
        return $this->ratings()->count();
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}