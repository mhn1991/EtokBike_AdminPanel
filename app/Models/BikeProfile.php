<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'customer_profile_id',
    'title',
    'subtitle',
    'frame_size',
    'tire_size',
    'brake_type',
    'next_recommendation',
    'sort_order',
    'is_active',
])]
class BikeProfile extends Model
{
    public function customerProfile(): BelongsTo
    {
        return $this->belongsTo(CustomerProfile::class);
    }

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toMobilePayload(): array
    {
        return [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'fields' => collect([
                ['label' => 'سایز فریم', 'value' => $this->frame_size],
                ['label' => 'سایز تایر', 'value' => $this->tire_size],
                ['label' => 'ترمز', 'value' => $this->brake_type],
                ['label' => 'پیشنهاد بعدی', 'value' => $this->next_recommendation],
            ])
                ->filter(fn (array $field): bool => filled($field['value']))
                ->values()
                ->all(),
        ];
    }
}
