<?php

namespace Tests\Unit;

use App\Http\Middleware\RewriteIndexedAssetUrls;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class RewriteIndexedAssetUrlsTest extends TestCase
{
    public function test_it_rewrites_public_asset_urls_when_asset_url_uses_index_php(): void
    {
        config(['app.asset_url' => 'https://etokbike.ir/App/index.php']);

        $response = (new RewriteIndexedAssetUrls())->handle(
            Request::create('/admin/login'),
            fn (): Response => new Response(
                '<link href="https://etokbike.ir/App/css/filament/filament/app.css">' .
                '<script src="https://etokbike.ir/App/js/filament/filament/app.js"></script>' .
                '<img src="https://etokbike.ir/App/images/storefront/hero-shop.png">' .
                '<img src="https://etokbike.ir/App/storage/mobile/products/bike.jpg">' .
                '<link href="https://etokbike.ir/App/favicon.ico">',
                headers: ['Content-Type' => 'text/html; charset=UTF-8'],
            ),
        );

        $this->assertStringContainsString(
            'https://etokbike.ir/App/index.php/css/filament/filament/app.css',
            $response->getContent(),
        );
        $this->assertStringContainsString(
            'https://etokbike.ir/App/index.php/js/filament/filament/app.js',
            $response->getContent(),
        );
        $this->assertStringContainsString(
            'https://etokbike.ir/App/index.php/images/storefront/hero-shop.png',
            $response->getContent(),
        );
        $this->assertStringContainsString(
            'https://etokbike.ir/App/index.php/storage/mobile/products/bike.jpg',
            $response->getContent(),
        );
        $this->assertStringContainsString(
            'https://etokbike.ir/App/index.php/favicon.ico',
            $response->getContent(),
        );
    }

    public function test_it_leaves_standard_asset_urls_unchanged(): void
    {
        config(['app.asset_url' => 'https://etokbike.ir/App']);

        $response = (new RewriteIndexedAssetUrls())->handle(
            Request::create('/admin/login'),
            fn (): Response => new Response(
                '<link href="https://etokbike.ir/App/css/filament/filament/app.css">',
                headers: ['Content-Type' => 'text/html; charset=UTF-8'],
            ),
        );

        $this->assertSame(
            '<link href="https://etokbike.ir/App/css/filament/filament/app.css">',
            $response->getContent(),
        );
    }
}
