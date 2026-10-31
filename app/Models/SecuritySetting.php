<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecuritySetting extends Model
{
    protected $fillable = [
        'user_id',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_confirmed_at',
        'new_device_login_alert',
        'successful_login_alert',
        'failed_login_alert',
        'suspicious_login_alert',
        'password_change_alert',
        'two_factor_security_alert',
        'last_password_changed_at',
        'last_successful_login_at',
    ];

    protected function casts(): array
    {
        return [
            'two_factor_enabled' => 'boolean',
            'two_factor_secret' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
            'new_device_login_alert' => 'boolean',
            'successful_login_alert' => 'boolean',
            'failed_login_alert' => 'boolean',
            'suspicious_login_alert' => 'boolean',
            'password_change_alert' => 'boolean',
            'two_factor_security_alert' => 'boolean',
            'last_password_changed_at' => 'datetime',
            'last_successful_login_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
