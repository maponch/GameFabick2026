<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Type extends Model
{
    use SoftDeletes;
    
    public $timestamps = false;

    protected $fillable = ['name'];

    protected $dates = ['deleted_at'];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function templates()
    {
        return $this->hasMany(GameTemplate::class);
    }
}