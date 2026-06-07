<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectObject extends Model
{
    protected $table = 'project_objects';

    protected $fillable = [
        'project_id',
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
        'custom_data'           => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}