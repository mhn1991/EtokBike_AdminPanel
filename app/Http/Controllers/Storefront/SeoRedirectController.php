<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\SeoRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SeoRedirectController extends Controller
{
    public function __invoke(Request $request, string $path = ''): RedirectResponse
    {
        $sourcePath = '/'.ltrim($path, '/');

        $redirect = SeoRedirect::query()
            ->where('is_active', true)
            ->where('source_path', $sourcePath)
            ->first();

        abort_unless($redirect, 404);

        $redirect->increment('hit_count');
        $redirect->forceFill(['last_hit_at' => now()])->save();

        return redirect()->to($redirect->target_url, $redirect->status_code);
    }
}
