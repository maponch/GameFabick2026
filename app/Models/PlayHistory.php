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
        'snapshot_data',
    ];

    protected $casts = [
        'played_at' => 'datetime',
        'snapshot_data' => 'array',
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