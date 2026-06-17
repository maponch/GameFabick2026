<?php

namespace App\Models;

use App\Models\GameFormat;
use App\Models\ProjectObject;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\Publishable;

class Project extends Model
{
    use Publishable;
    
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
    ];

    protected $casts = [
        'card_schema' => 'array',
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
}