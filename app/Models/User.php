<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'profile_photo',
        'scheduled_deletion_at',
        'two_factor_method',
        'two_factor_secret',
        'two_factor_enabled',
        'two_factor_recovery_codes',
        'deletion_initiator',
    ];
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'scheduled_deletion_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
            'two_factor_recovery_codes' => 'array',
            'two_factor_secret' => 'encrypted',
        ];
    }
    protected $appends = ['photo_profile_url'];

    public function getPhotoProfileUrlAttribute()
    {
        return $this->profile_photo
            ? asset('storage/' . $this->profile_photo)
            : asset('images/default-profile.png');
    }

    // Relations avec les suspensions
    public function suspensions()
    {
        return $this->hasMany(Suspension::class);
    }

    public function suspensionHistory()
    {
        return $this->hasOne(SuspensionHistory::class);
    }

    // Retourne la suspension active si elle existe
    public function activeSuspension(): ?Suspension
    {
        $suspension = $this->suspensions()
            ->where('is_active', true)
            ->latest()
            ->first();

        if (!$suspension) {
            return null;
        }

        if ($suspension->expires_at && now()->isAfter($suspension->expires_at)) {
            $suspension->update(['is_active' => false]);
            return null;
        }

        return $suspension;
    }

    public function isSuspended(): bool
    {
        return $this->activeSuspension() !== null;
    }

    public function scheduleDeletion(string $initiator = 'self'): void
    {
        $this->update([
            'scheduled_deletion_at' => now()->addDays(30),
            'deletion_initiator'    => $initiator,
        ]);
        $this->delete(); // soft delete
    }

    // Restaure le compte
    public function restoreAccount(): void
    {
        $this->restore();
        $this->update(['scheduled_deletion_at' => null, 'deletion_initiator' => null]);
    }

    public function isPendingDeletion(): bool
    {
        return $this->trashed() && $this->scheduled_deletion_at !== null;
    }
}
