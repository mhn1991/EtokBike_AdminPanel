<?php

namespace Tests\Unit;

use App\Support\Mobile\ImageUrl;
use Tests\TestCase;

class ImageUrlTest extends TestCase
{
    public function test_it_resolves_storefront_storage_urls_through_asset_url(): void
    {
        config([
            'app.asset_url' => 'https://etokbike.ir/App/index.php',
            'app.url' => 'https://etokbike.ir/App',
        ]);

        $this->assertSame(
            'https://etokbike.ir/App/index.php/storage/mobile/products/bike.jpg',
            ImageUrl::resolve('mobile/products/bike.jpg'),
        );
    }

    public function test_it_resolves_mobile_storage_urls_through_mobile_base_url(): void
    {
        config([
            'app.url' => 'http://127.0.0.1:8001',
            'mobile.base_url' => 'http://10.0.2.2:8001',
        ]);

        $this->assertSame(
            'http://10.0.2.2:8001/storage/mobile/products/bike.jpg',
            ImageUrl::resolveForMobile('mobile/products/bike.jpg'),
        );
    }

    public function test_it_preserves_absolute_image_urls(): void
    {
        $this->assertSame(
            'https://cdn.example.com/bike.jpg',
            ImageUrl::resolveForMobile('https://cdn.example.com/bike.jpg'),
        );
    }
}
