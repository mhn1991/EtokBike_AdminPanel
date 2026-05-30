<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'screen_id',
    'title',
    'version',
    'hide_title',
    'is_active',
])]
class MobileScreen extends Model
{
    public const SCREEN_OPTIONS = [
        'home' => 'Home',
        'shop' => 'Shop',
        'services' => 'Services',
        'events' => 'Events',
        'account' => 'Account',
        'messages' => 'Messages',
        'cart' => 'Cart',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(MobileScreenSection::class);
    }

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'hide_title' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  array<string, mixed>  $fallback
     * @return array<string, mixed>
     */
    public function toMobilePayload(array $fallback): array
    {
        $sections = $this->sections
            ->where('is_active', true)
            ->sortBy([
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->map(fn (MobileScreenSection $section): array => $section->toMobilePayload())
            ->values()
            ->all();

        if (empty($sections)) {
            return $fallback;
        }

        return [
            'schemaVersion' => 1,
            'screenId' => $this->screen_id,
            'version' => $this->mobileVersion(),
            'title' => $this->title,
            'hideTitle' => $this->hide_title,
            'sections' => $sections,
        ];
    }

    public function mobileVersion(): int
    {
        $sectionTimestamp = $this->relationLoaded('sections')
            ? $this->sections->max(fn (MobileScreenSection $section): int => $section->updated_at?->getTimestamp() ?? 0)
            : 0;

        return max(
            $this->version,
            $this->updated_at?->getTimestamp() ?? 0,
            $sectionTimestamp ?: 0,
        );
    }
}
