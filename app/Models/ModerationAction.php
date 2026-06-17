<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModerationAction extends Model
{
    public const ACTION_DEPUBLISH = 'depublish';
    public const ACTION_ARCHIVE   = 'archive';

    public const REASON_SPAM          = 'spam';
    public const REASON_INAPPROPRIATE = 'inappropriate';
    public const REASON_LOW_QUALITY   = 'low_quality';
    public const REASON_COPYRIGHT     = 'copyright';
    public const REASON_OTHER         = 'other';

    public const REASON_LABELS = [
        self::REASON_SPAM          => 'Spam ou publicité',
        self::REASON_INAPPROPRIATE => 'Contenu inapproprié',
        self::REASON_LOW_QUALITY   => 'Qualité insuffisante',
        self::REASON_COPYRIGHT     => 'Violation de droits d\'auteur',
        self::REASON_OTHER         => 'Autre',
    ];

    protected $fillable = [
        'project_id',
        'user_id_targeted',
        'admin_id',
        'action',
        'reason_code',
        'reason_text',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function targetedUser()
    {
        return $this->belongsTo(User::class, 'user_id_targeted');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function reasonLabel(): string
    {
        return self::REASON_LABELS[$this->reason_code] ?? $this->reason_code;
    }
}