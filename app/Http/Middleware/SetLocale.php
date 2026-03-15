<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED_LOCALES = ['ka', 'en', 'ru'];

    public function handle(Request $request, Closure $next): Response
    {
        $segments = $request->segments();
        $locale = $segments[0] ?? null;

        if (in_array($locale, ['en', 'ru'])) {
            app()->setLocale($locale);
        } else {
            app()->setLocale('ka');
        }

        return $next($request);
    }

    public static function supportedLocales(): array
    {
        return self::SUPPORTED_LOCALES;
    }
}
