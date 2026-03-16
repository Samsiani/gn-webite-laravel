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
        // For Livewire/internal requests, read locale from session
        $path = $request->path();
        if (str_starts_with($path, 'livewire/') || str_starts_with($path, 'admin')) {
            $locale = session('locale', 'ka');
            app()->setLocale($locale);
            return $next($request);
        }

        $segments = $request->segments();
        $locale = $segments[0] ?? null;

        if (in_array($locale, ['en', 'ru'])) {
            app()->setLocale($locale);
        } else {
            $locale = 'ka';
            app()->setLocale($locale);
        }

        // Persist locale in session for Livewire requests
        session(['locale' => $locale]);

        return $next($request);
    }

    public static function supportedLocales(): array
    {
        return self::SUPPORTED_LOCALES;
    }
}
