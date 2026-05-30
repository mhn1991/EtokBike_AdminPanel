<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Mobile\AccountScreenBuilder;
use App\Support\Mobile\CartScreenBuilder;
use App\Support\Mobile\DatabaseScreenBuilder;
use App\Support\Mobile\EventsScreenBuilder;
use App\Support\Mobile\HomeScreenBuilder;
use App\Support\Mobile\MessagesScreenBuilder;
use App\Support\Mobile\ServicesScreenBuilder;
use App\Support\Mobile\ShopScreenBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MobileConfigController extends Controller
{
    /**
     * @var array<string, class-string>
     */
    private const SCREEN_BUILDERS = [
        'home' => HomeScreenBuilder::class,
        'shop' => ShopScreenBuilder::class,
        'services' => ServicesScreenBuilder::class,
        'events' => EventsScreenBuilder::class,
        'account' => AccountScreenBuilder::class,
        'messages' => MessagesScreenBuilder::class,
        'cart' => CartScreenBuilder::class,
    ];

    public function manifest(): JsonResponse
    {
        $manifest = $this->readJsonFile(config('mobile.manifest_path'));
        $screens = Arr::get($manifest, 'screens', []);

        Arr::set($manifest, 'remoteConfig.manifestUrl', $this->mobileUrl(route('mobile.manifest', absolute: false)));

        foreach (array_keys($screens) as $screenId) {
            Arr::set($manifest, "screens.{$screenId}.url", $this->mobileUrl(route('mobile.screens.show', ['screen' => $screenId], false)));
            Arr::set($manifest, "screens.{$screenId}.checksum", '');
        }

        foreach (self::SCREEN_BUILDERS as $screenId => $builder) {
            if (! array_key_exists($screenId, $screens)) {
                continue;
            }

            $screen = DatabaseScreenBuilder::build($this->readScreenJson($screenId));
            $version = $builder::version($screen);

            Arr::set($manifest, "screens.{$screenId}.version", $version);
            Arr::set($manifest, 'appVersion', max((int) ($manifest['appVersion'] ?? 1), $version));
        }

        return $this->mobileJson($manifest);
    }

    public function screen(string $screen): JsonResponse
    {
        $payload = DatabaseScreenBuilder::build($this->readScreenJson($screen));

        if (array_key_exists($screen, self::SCREEN_BUILDERS)) {
            $payload = self::SCREEN_BUILDERS[$screen]::build($payload);
        }

        if (($payload['screenId'] ?? null) !== $screen) {
            abort(500, 'Mobile screen payload does not match requested screen.');
        }

        return $this->mobileJson($payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function readScreenJson(string $screen): array
    {
        $path = rtrim(config('mobile.screens_path'), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$screen.'.json';

        if (! File::isFile($path)) {
            throw new NotFoundHttpException('Mobile screen not found.');
        }

        return $this->readJsonFile($path);
    }

    /**
     * @return array<string, mixed>
     */
    private function readJsonFile(string $path): array
    {
        if (! File::isFile($path)) {
            throw new NotFoundHttpException('Mobile config file not found.');
        }

        $payload = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($payload)) {
            abort(500, 'Mobile config file must contain a JSON object.');
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mobileJson(array $payload): JsonResponse
    {
        return Response::json($payload)
            ->header('Cache-Control', 'no-cache, private')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    private function mobileUrl(string $path): string
    {
        $baseUrl = config('mobile.base_url');

        if (blank($baseUrl)) {
            return url($path);
        }

        return rtrim((string) $baseUrl, '/').'/'.ltrim($path, '/');
    }
}
