<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'notification_template_id',
    'order_id',
    'channel',
    'recipient',
    'subject',
    'body',
    'status',
    'sent_at',
    'failure_reason',
])]
class NotificationLog extends Model
{
    public const STATUS_OPTIONS = [
        'pending' => 'Pending',
        'sent' => 'Sent',
        'failed' => 'Failed',
        'cancelled' => 'Cancelled',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'notification_template_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }
}
