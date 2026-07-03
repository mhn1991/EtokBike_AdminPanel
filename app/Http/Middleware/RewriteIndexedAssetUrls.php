<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RewriteIndexedAssetUrls
{
    /**
     * Rewrite generated asset URLs for hosts that only route assets through index.php.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $indexedAssetUrl = rtrim((string) config('app.asset_url'), '/');

        if ($indexedAssetUrl === '' || ! str_ends_with($indexedAssetUrl, '/index.php')) {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type');

        if ($contentType !== '' && ! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $content = $response->getContent();

        if (! is_string($content) || $content === '') {
            return $response;
        }

        $directAssetUrl = substr($indexedAssetUrl, 0, -strlen('/index.php'));

        $content = str_replace(
            [
                "{$directAssetUrl}/build/",
                "{$directAssetUrl}/css/",
                "{$directAssetUrl}/fonts/",
                "{$directAssetUrl}/images/",
                "{$directAssetUrl}/js/",
                "{$directAssetUrl}/storage/",
                "{$directAssetUrl}/favicon.ico",
            ],
            [
                "{$indexedAssetUrl}/build/",
                "{$indexedAssetUrl}/css/",
                "{$indexedAssetUrl}/fonts/",
                "{$indexedAssetUrl}/images/",
                "{$indexedAssetUrl}/js/",
                "{$indexedAssetUrl}/storage/",
                "{$indexedAssetUrl}/favicon.ico",
            ],
            $content,
        );

        $response->setContent($content);
        $response->headers->remove('Content-Length');

        return $response;
    }
}
