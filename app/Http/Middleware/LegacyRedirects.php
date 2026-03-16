<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class LegacyRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();

        // Skip internal routes
        if (str_starts_with($path, 'livewire/') || str_starts_with($path, 'admin') || str_starts_with($path, 'filament/')) {
            return $next($request);
        }

        // Redirect /product-category/* → /category/*
        if (preg_match('#^(en/|ru/)?product-category/(.+?)/?$#', $path, $m)) {
            $prefix = $m[1] ? '/' . rtrim($m[1], '/') : '';
            return redirect("{$prefix}/category/{$m[2]}", 301);
        }

        // Strip trailing slashes (except root)
        if ($path !== '/' && str_ends_with($path, '/')) {
            return redirect('/' . rtrim($path, '/'), 301);
        }

        // Check cached redirect map (refreshes every hour)
        try {
            $redirectMap = Cache::remember('redirect_map', 3600, function () {
                return DB::table('redirect_map')->pluck('new_url', 'old_url')->toArray();
            });

            $lookupPath = '/' . $path;
            if (isset($redirectMap[$lookupPath])) {
                return redirect($redirectMap[$lookupPath], 301);
            }
        } catch (\Exception $e) {
            // Table may not exist yet
        }

        return $next($request);
    }
}
