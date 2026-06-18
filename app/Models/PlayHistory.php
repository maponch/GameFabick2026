<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayHistory extends Model
{
    protected $table = 'play_history';

    protected $fillable = [
        'user_id',
        'project_id',
        'played_at',
        'note',
    ];

    protected $casts = [
        'played_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}