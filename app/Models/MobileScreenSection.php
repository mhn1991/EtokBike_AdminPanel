<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'mobile_screen_id',
    'section_id',
    'type',
    'data',
    'layout',
    'style',
    'data_json',
    'layout_json',
    'style_json',
    'sort_order',
    'is_active',
])]
class MobileScreenSection extends Model
{
    public const TYPE_OPTIONS = [
        'hero' => 'Hero',
        'category_grid' => 'Category grid',
        'product_row' => 'Product row',
        'offer_sections' => 'Offer sections',
        'program_sections' => 'Program sections',
        'product_list' => 'Product list',
        'service_list' => 'Service list',
        'schedule_list' => 'Schedule list',
        'activity_list' => 'Activity list',
        'client_details' => 'Client details',
        'purchase_history' => 'Purchase history',
        'ongoing_purchase' => 'Ongoing purchase',
        'message_center' => 'Message center',
        'cart_summary' => 'Cart summary',
        'service_booking_form' => 'Service booking form',
        'status_tracker' => 'Status tracker',
        'bike_profile_list' => 'Bike profile list',
        'business_info' => 'Business info',
        'checkout_note' => 'Checkout note',
        'profile_summary' => 'Profile summary',
    ];

    public function mobileScreen(): BelongsTo
    {
        return $this->belongsTo(MobileScreen::class);
    }

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'layout' => 'array',
            'style' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toMobilePayload(): array
    {
        $payload = [
            'id' => $this->section_id,
            'type' => $this->type,
            'data' => $this->data ?: [],
        ];

        if ($this->layout !== null) {
            $payload['layout'] = $this->layout;
        }

        if ($this->style !== null) {
            $payload['style'] = $this->style;
        }

        return $payload;
    }

    protected function dataJson(): Attribute
    {
        return $this->jsonAttribute('data');
    }

    protected function layoutJson(): Attribute
    {
        return $this->jsonAttribute('layout');
    }

    protected function styleJson(): Attribute
    {
        return $this->jsonAttribute('style');
    }

    private function jsonAttribute(string $attribute): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->formatJson($this->{$attribute}),
            set: fn (?string $value): array => [$attribute => $this->decodeJson($value)],
        );
    }

    private function formatJson(mixed $value): string
    {
        return json_encode($value ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }

    /**
     * @return array<mixed>
     */
    private function decodeJson(?string $value): array
    {
        if (blank($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }
}
