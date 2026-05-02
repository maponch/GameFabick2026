<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuspensionHistory extends Model
{
    protected $table = 'suspension_history';
    
    protected $fillable = [
        'user_id',
        'email_hash',
        'suspension_count',
        'is_permanently_banned',
    ];

    protected $casts = [
        'is_permanently_banned' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}