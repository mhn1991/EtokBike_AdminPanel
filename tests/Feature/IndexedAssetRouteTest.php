<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class IndexedAssetRouteTest extends TestCase
{
    public function test_it_serves_public_storage_uploads_through_the_storage_route(): void
    {
        $path = storage_path('app/public/mobile/products/storage-route-test.jpg');

        File::ensureDirectoryExists(dirname($path));
        File::put($path, 'fake image');

        try {
            $response = $this->get('/storage/mobile/products/storage-route-test.jpg')
                ->assertOk()
                ->assertHeader('Content-Type', 'image/jpeg');

            $this->assertSame($path, $response->baseResponse->getFile()->getPathname());
        } finally {
            File::delete($path);
        }
    }

    public function test_it_serves_storage_uploads_through_the_indexed_asset_route(): void
    {
        $path = storage_path('app/public/mobile/products/indexed-route-test.jpg');

        File::ensureDirectoryExists(dirname($path));
        File::put($path, 'fake image');

        try {
            $response = $this->get('/index.php/storage/mobile/products/indexed-route-test.jpg')
                ->assertOk()
                ->assertHeader('Content-Type', 'image/jpeg');

            $this->assertSame($path, $response->baseResponse->getFile()->getPathname());
        } finally {
            File::delete($path);
        }
    }
}
