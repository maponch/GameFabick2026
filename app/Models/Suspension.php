<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suspension extends Model
{
    protected $fillable = [
        'user_id',
        'admin_id',
        'reason',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active'  => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Vérifie si la suspension est expirée et la désactive
    public function checkExpiry(): void
    {
        if ($this->is_active && $this->expires_at && now()->isAfter($this->expires_at)) {
            $this->update(['is_active' => false]);
        }
    }
}