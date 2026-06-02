<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GameFormat extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['name', 'slug'];

    protected $dates = ['deleted_at'];

    public function templates()
    {
        return $this->belongsToMany(GameTemplate::class, 'game_template_format')
                    ->withTimestamps();
    }
}