<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'slug',
    'title',
    'excerpt',
    'body',
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
    'is_active',
    'sort_order',
])]
class ContentPage extends Model
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

    protected function casts(): array
    {
        return [
            'include_in_sitemap' => 'boolean',
            'sitemap_priority' => 'decimal:1',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
