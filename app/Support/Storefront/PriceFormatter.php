<?php

namespace App\Support\Storefront;

class PriceFormatter
{
    public static function format(int $amount, string $currency = 'IRR'): string
    {
        return number_format($amount).' تومان';
    }
}
