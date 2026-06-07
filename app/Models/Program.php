<?php

namespace App\Models;

use App\Support\Mobile\ImageUrl;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'program_category_id',
    'slug',
    'title',
    'subtitle',
    'date_value',
    'date_label',
    'program_state',
    'status_label',
    'book_label',
    'view_label',
    'ad_title',
    'advertisement',
    'details',
    'gallery_title',
    'thumbnail_text',
    'thumbnail_color',
    'image_url',
    'capacity',
    'reserved_count',
    'sort_order',
    'is_active',
])]
class Program extends Model
{
    public const STATE_OPTIONS = [
        'future' => 'Future',
        'finished' => 'Finished',
        'cancelled' => 'Cancelled',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProgramCategory::class, 'program_category_id');
    }

    public function galleryItems(): HasMany
    {
        return $this->hasMany(ProgramGalleryItem::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ProgramBooking::class);
    }

    public function refreshReservedCountFromBookings(): void
    {
        $reservedCount = $this->bookings()
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->sum('attendees');

        $this->forceFill([
            'reserved_count' => $reservedCount,
        ])->saveQuietly();
    }

    protected function casts(): array
    {
        return [
            'date_value' => 'date',
            'details' => 'array',
            'capacity' => 'integer',
            'reserved_count' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Program $program): void {
            $program->status_label = $program->status_label ?: match ($program->program_state) {
                'future' => 'برنامه آینده',
                'finished' => 'برنامه برگزار شده',
                'cancelled' => 'لغو شده',
                default => null,
            };

            $program->book_label = $program->program_state === 'future'
                ? ($program->book_label ?: 'رزرو برنامه')
                : null;

            $program->view_label = $program->program_state === 'finished'
                ? ($program->view_label ?: 'مشاهده برنامه')
                : null;
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function toMobilePayload(): array
    {
        $payload = [
            'id' => $this->slug,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'dateValue' => $this->date_value?->toDateString(),
            'dateLabel' => $this->date_label,
            'programState' => $this->program_state,
            'statusLabel' => $this->status_label,
            'adTitle' => $this->ad_title ?: $this->title,
            'advertisement' => $this->advertisement,
            'details' => $this->details ?: [],
            'thumbnailText' => $this->thumbnail_text,
            'thumbnailColor' => $this->thumbnail_color,
            'imageUrl' => ImageUrl::resolve($this->image_url),
        ];

        if ($this->program_state === 'future') {
            $payload['bookLabel'] = $this->book_label ?: 'رزرو برنامه';
        }

        if ($this->program_state === 'finished') {
            $payload['viewLabel'] = $this->view_label ?: 'مشاهده برنامه';
            $payload['galleryTitle'] = $this->gallery_title;
            $payload['gallery'] = $this->galleryItems
                ->sortBy('sort_order')
                ->values()
                ->map(fn (ProgramGalleryItem $item): array => $item->toMobilePayload())
                ->all();
        }

        return $payload;
    }
}
