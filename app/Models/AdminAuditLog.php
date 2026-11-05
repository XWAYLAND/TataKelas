<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAuditLog extends Model
{
    public const ROLE_CHANGED = 'role_changed';

    public const PASSWORD_RESET = 'password_reset';

    public const ACCOUNT_DELETED = 'account_deleted';

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
}
