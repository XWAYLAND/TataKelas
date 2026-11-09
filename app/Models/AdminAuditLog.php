<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAuditLog extends Model
{
    public const ROLE_CHANGED = 'role_changed';

    public const PASSWORD_RESET = 'password_reset';

    public const ACCOUNT_DELETED = 'account_deleted';

    public const ACCOUNT_SUSPENDED = 'account_suspended';

    public const ACCOUNT_REACTIVATED = 'account_reactivated';

    private const ACTION_LABELS = [
        self::ROLE_CHANGED => 'Role diubah',
        self::PASSWORD_RESET => 'Password direset',
        self::ACCOUNT_DELETED => 'Akun dihapus',
        self::ACCOUNT_SUSPENDED => 'Akun ditangguhkan',
        self::ACCOUNT_REACTIVATED => 'Akun diaktifkan kembali',
    ];

    protected $fillable = [
        'actor_user_id',
        'actor_name',
        'actor_email',
        'target_user_id',
        'target_name',
        'target_email',
        'action',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function getActionLabelAttribute(): string
    {
        return self::ACTION_LABELS[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }
}
