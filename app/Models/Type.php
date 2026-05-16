<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    public $timestamps = false;

    protected $fillable = ['name'];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function templates()
    {
        return $this->hasMany(GameTemplate::class);
    }
}