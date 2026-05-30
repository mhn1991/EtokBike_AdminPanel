<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'message_department_id',
    'user_id',
    'sender',
    'label',
    'text',
    'time_label',
    'is_unread',
])]
class CustomerMessage extends Model
{
    public const SENDER_OPTIONS = [
        'client' => 'Client',
        'department' => 'Department',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(MessageDepartment::class, 'message_department_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return ['is_unread' => 'boolean'];
    }
}
