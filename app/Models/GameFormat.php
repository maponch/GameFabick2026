<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameFormat extends Model
{
    protected $fillable = ['name', 'slug'];

    public function templates()
    {
        return $this->belongsToMany(GameTemplate::class, 'game_template_format')
                    ->withTimestamps();
    }
}