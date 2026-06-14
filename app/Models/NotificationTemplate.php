<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'key',
    'channel',
    'subject',
    'body',
    'is_active',
])]
class NotificationTemplate extends Model
{
    public const CHANNEL_OPTIONS = [
        'email' => 'Email',
        'sms' => 'SMS',
        'whatsapp' => 'WhatsApp',
        'push' => 'Push',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
