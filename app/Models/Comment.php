<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['user_id', 'template_id', 'project_id', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(GameTemplate::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}