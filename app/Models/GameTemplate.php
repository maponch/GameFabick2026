<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameTemplate extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PUBLISHED,
        self::STATUS_ARCHIVED,
    ];
    protected $fillable = [
        'name',
        'slug',
        'description',
        'rules',
        'type_id',
        'min_players',
        'max_players',
        'duration_min',
        'duration_max',
        'supports_existing_deck',
        'created_by',
        'status',
        'card_schema',
        'card_layout',
    ];

    protected $casts = [
        'supports_existing_deck' => 'boolean',
        'card_schema'            => 'array',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function type()
    {
        return $this->belongsTo(Type::class)->withTrashed();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function objects()
    {
        return $this->belongsToMany(GameObject::class, 'game_template_objects', 'game_template_id', 'object_id')
                    ->withTimestamps();
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'template_id');
    }
    public function formats()
    {
        return $this->belongsToMany(GameFormat::class, 'game_template_format')
                    ->withTimestamps()
                    ->withTrashed();
    }
    public function publishabilityReport(): array
    {
        $missing = [];

        if (empty(trim((string) $this->description)) || mb_strlen($this->description) < 10) {
            $missing[] = 'description trop courte (minimum 10 caractères)';
        }

        if (empty(trim((string) $this->rules)) || mb_strlen($this->rules) < 50) {
            $missing[] = 'règles trop courtes (minimum 50 caractères)';
        }

        $this->loadMissing('formats', 'objects');

        $formats = $this->formats;
        $formatSlugs = $formats->pluck('slug')->all();

        if ($formats->isEmpty()) {
            $missing[] = 'au moins un format de jeu';
        }

        if (in_array('impression', $formatSlugs, true)) {
            if ($this->objects->count() < 2) {
                $missing[] = 'au moins 2 cartes (format impression)';
            }
        }

        if (in_array('cartes-classiques', $formatSlugs, true)) {
            $orphans = [];
            foreach ($this->objects as $object) {
                $mappingCount = is_array($object->existing_deck_mapping)
                    ? count($object->existing_deck_mapping)
                    : 0;
                if ($mappingCount < $object->quantity) {
                    $orphans[] = "\"{$object->name}\" ({$mappingCount}/{$object->quantity})";
                }
            }
            if (!empty($orphans)) {
                $missing[] = 'Correspondances cartes insuffisantes : ' . implode(', ', $orphans);
            }
        }

        return [
            'ready'   => empty($missing),
            'missing' => $missing,
        ];
    }
}