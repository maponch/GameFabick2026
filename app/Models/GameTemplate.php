<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\Publishable;

class GameTemplate extends Model
{
    use Publishable; 

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
    public function ratings()
    {
        return $this->hasMany(Rating::class, 'template_id');
    }

    public function averageRating(): float
    {
        return round($this->ratings()->avg('score') ?? 0, 1);
    }

    public function ratingsCount(): int
    {
        return $this->ratings()->count();
    }
    
}