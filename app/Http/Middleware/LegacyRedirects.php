<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class LegacyRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();

        // Redirect /product-category/* → /category/*
        if (preg_match('#^(en/|ru/)?product-category/(.+?)/?$#', $path, $m)) {
            $prefix = $m[1] ? '/' . rtrim($m[1], '/') : '';
            return redirect("{$prefix}/category/{$m[2]}", 301);
        }

        // Strip trailing slashes (except root)
        if ($path !== '/' && str_ends_with($path, '/')) {
            return redirect('/' . rtrim($path, '/'), 301);
        }

        // Check redirect_map table for custom redirects
        try {
            $redirect = DB::table('redirect_map')
                ->where('old_url', '/' . $path)
                ->first();

            if ($redirect) {
                return redirect($redirect->new_url, 301);
            }
        } catch (\Exception $e) {
            // Table may not exist yet
        }

        return $next($request);
    }
}
