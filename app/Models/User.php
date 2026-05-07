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
        return $this->suspensions()
            ->where('is_active', true)
            ->latest()
            ->first()
            ?->tap(fn($s) => $s->checkExpiry()) // vérifie l'expiration au passage
            ?? null;
    }

    public function isSuspended(): bool
    {
        return $this->activeSuspension() !== null;
    }

    // Démarre le processus de suppression
    public function scheduleDeletion(): void
    {
        $this->update([
            'scheduled_deletion_at' => now()->addDays(30),
        ]);
        $this->delete(); // soft delete
    }

    // Restaure le compte
    public function restoreAccount(): void
    {
        $this->restore();
        $this->update(['scheduled_deletion_at' => null]);
    }

    public function isPendingDeletion(): bool
    {
        return $this->trashed() && $this->scheduled_deletion_at !== null;
    }
    public function markForDeletion(): void
    {
        $this->update([
            'scheduled_deletion_at' => now()->addDays(30),
        ]);
    }
}
