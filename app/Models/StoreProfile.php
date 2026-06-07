<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'status_title',
    'status_subtitle',
    'status_description',
    'status_label',
    'branch_title',
    'address',
    'hours',
    'action_label',
    'is_active',
])]
class StoreProfile extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public function statusMobilePayload(): array
    {
        return [
            'title' => $this->status_title,
            'subtitle' => $this->status_subtitle,
            'description' => $this->status_description,
            'price' => $this->status_label,
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public function infoMobilePayload(): array
    {
        return [
            'title' => $this->branch_title,
            'subtitle' => $this->address,
            'description' => $this->hours,
            'price' => $this->action_label,
        ];
    }
}
