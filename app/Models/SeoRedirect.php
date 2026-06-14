<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'source_path',
    'target_url',
    'status_code',
    'is_active',
    'hit_count',
    'last_hit_at',
    'notes',
])]
class SeoRedirect extends Model
{
    public const STATUS_CODE_OPTIONS = [
        301 => '301 Permanent',
        302 => '302 Temporary',
    ];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'is_active' => 'boolean',
            'hit_count' => 'integer',
            'last_hit_at' => 'datetime',
        ];
    }

    public function normalizedSourcePath(): string
    {
        return '/'.ltrim($this->source_path, '/');
    }

    public function setSourcePathAttribute(string $value): void
    {
        $this->attributes['source_path'] = '/'.ltrim($value, '/');
    }
}
