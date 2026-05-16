<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'user_id',
        'type_id',
        'template_id',
        'title',
        'description',
        'mode',
        'duration',
        'min_players',
        'max_players',
        'status',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
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
}