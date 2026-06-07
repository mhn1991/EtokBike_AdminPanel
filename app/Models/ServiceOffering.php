<?php

namespace App\Models;

use App\Support\Mobile\ImageUrl;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_category_id',
    'slug',
    'title',
    'subtitle',
    'description',
    'price_label',
    'thumbnail_text',
    'thumbnail_color',
    'image_url',
    'sort_order',
    'is_active',
])]
class ServiceOffering extends Model
{
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toMobilePayload(): array
    {
        return [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'price' => $this->price_label,
            'thumbnailText' => $this->thumbnail_text,
            'thumbnailColor' => $this->thumbnail_color,
            'imageUrl' => ImageUrl::resolve($this->image_url),
        ];
    }
}
