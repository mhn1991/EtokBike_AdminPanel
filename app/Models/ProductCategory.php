<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'slug',
    'label',
    'seo_title',
    'seo_description',
    'canonical_url',
    'robots',
    'og_title',
    'og_description',
    'og_image',
    'include_in_sitemap',
    'sitemap_priority',
    'sitemap_change_frequency',
    'sort_order',
    'is_active',
])]
class ProductCategory extends Model
{
    public const ROBOTS_OPTIONS = [
        'index,follow' => 'Index, follow',
        'noindex,follow' => 'No index, follow',
        'noindex,nofollow' => 'No index, no follow',
    ];

    public const CHANGE_FREQUENCY_OPTIONS = [
        'always' => 'Always',
        'hourly' => 'Hourly',
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
        'never' => 'Never',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected function casts(): array
    {
        return [
            'include_in_sitemap' => 'boolean',
            'sitemap_priority' => 'decimal:1',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
