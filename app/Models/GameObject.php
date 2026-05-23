<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameObject extends Model
{
    protected $table = 'objects';

    protected $fillable = [
        'name',
        'description',
        'quantity',
        'default_color',
        'default_image_path',
        'existing_deck_mapping',
        'custom_data',
    ];

    protected $casts = [
        'existing_deck_mapping' => 'array',
        'custom_data' => 'array',
    ];

    public function templates()
    {
        return $this->belongsToMany(GameTemplate::class, 'game_template_objects', 'object_id', 'game_template_id')
                    ->withTimestamps();
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'object_project')
                    ->withPivot(['custom_image_id', 'custom_text', 'custom_color'])
                    ->withTimestamps();
    }
}