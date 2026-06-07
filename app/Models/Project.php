<?php

namespace App\Models;

use App\Models\GameFormat;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
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
        return $this->belongsToMany(
            GameObject::class,
            'object_project',
            'project_id',  
            'object_id'      
        )
        ->withPivot(['custom_image_id', 'custom_text', 'custom_color'])
        ->withTimestamps();
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