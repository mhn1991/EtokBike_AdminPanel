<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'slug',
    'title',
    'subtitle',
    'thread_title',
    'composer_title',
    'placeholder',
    'send_label',
    'sort_order',
    'is_active',
])]
class MessageDepartment extends Model
{
    public function messages(): HasMany
    {
        return $this->hasMany(CustomerMessage::class);
    }

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean'];
    }
}
