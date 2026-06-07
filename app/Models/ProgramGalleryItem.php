<?php

namespace App\Models;

use App\Support\Mobile\ImageUrl;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'program_id',
    'thumbnail_text',
    'thumbnail_color',
    'caption',
    'image_url',
    'sort_order',
])]
class ProgramGalleryItem extends Model
{
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toMobilePayload(): array
    {
        return [
            'thumbnailText' => $this->thumbnail_text,
            'thumbnailColor' => $this->thumbnail_color,
            'caption' => $this->caption,
            'imageUrl' => ImageUrl::resolve($this->image_url),
        ];
    }
}
