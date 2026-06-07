<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'program_id',
    'user_id',
    'customer_name',
    'customer_phone',
    'customer_email',
    'attendees',
    'status',
    'customer_notes',
    'admin_notes',
])]
class ProgramBooking extends Model
{
    public const STATUS_OPTIONS = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'cancelled' => 'Cancelled',
        'attended' => 'Attended',
        'no_show' => 'No show',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'attendees' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (ProgramBooking $booking): void {
            $booking->program?->refreshReservedCountFromBookings();

            $originalProgramId = $booking->getOriginal('program_id');

            if ($originalProgramId && (int) $originalProgramId !== (int) $booking->program_id) {
                Program::query()->find($originalProgramId)?->refreshReservedCountFromBookings();
            }
        });

        static::deleted(fn (ProgramBooking $booking) => $booking->program?->refreshReservedCountFromBookings());
    }
}
