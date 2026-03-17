<?php

namespace App\Http\Middleware;

use App\Services\SeoHelper;
use Closure;
use Illuminate\Http\Request;
use Lunar\Models\Product;
use Lunar\Models\Url;

class ServeSocialCrawlers
{
    /**
     * Social crawler bots that need pre-rendered OG tags.
     */
    private array $bots = [
        'facebookexternalhit',
        'Facebot',
        'Twitterbot',
        'LinkedInBot',
        'WhatsApp',
        'Slackbot',
        'TelegramBot',
        'Pinterest',
        'Discordbot',
    ];

    public function handle(Request $request, Closure $next)
    {
        $ua = $request->userAgent() ?? '';

        if (! $this->isSocialBot($ua)) {
            return $next($request);
        }

        // Serve lightweight HTML with OG tags — no Livewire, no sessions, no challenge
        return $this->serveMeta($request);
    }

    private function isSocialBot(string $ua): bool
    {
        foreach ($this->bots as $bot) {
            if (stripos($ua, $bot) !== false) {
                return true;
            }
        }
        return false;
    }

    private function serveMeta(Request $request): \Illuminate\Http\Response
    {
        $path = $request->path();
        $locale = 'ka';
        if (str_starts_with($path, 'en/')) { $locale = 'en'; $path = substr($path, 3); }
        elseif (str_starts_with($path, 'ru/')) { $locale = 'ru'; $path = substr($path, 3); }

        $title = SeoHelper::title('Professional Kitchen Equipment');
        $description = SeoHelper::defaultDescription();
        $image = SeoHelper::defaultOgImage();
        $url = $request->url();
        $type = 'website';

        // Product page
        if (preg_match('#^product/(.+)$#', $path, $m)) {
            $slug = $m[1];
            $urlModel = Url::where('slug', $slug)->where('element_type', 'product')->first();
            if ($urlModel) {
                $product = Product::with(['variants.prices', 'media'])->find($urlModel->element_id);
                if ($product) {
                    $name = $product->translateAttribute('name', $locale) ?? $product->translateAttribute('name');
                    $desc = $product->translateAttribute('description', $locale) ?? $product->translateAttribute('description');
                    $title = SeoHelper::title($name);
                    $description = \Illuminate\Support\Str::limit(strip_tags($desc), 160);
                    $image = $product->getFirstMediaUrl('images', 'medium') ?: $product->getFirstMediaUrl('images') ?: $image;
                    $type = 'product';
                }
            }
        }

        // Category page
        if (preg_match('#^category/(.+)$#', $path, $m)) {
            $slug = $m[1];
            $urlModel = Url::where('slug', $slug)->where('element_type', 'collection')->first();
            if ($urlModel) {
                $collection = \Lunar\Models\Collection::find($urlModel->element_id);
                if ($collection) {
                    $name = $collection->translateAttribute('name', $locale) ?? $collection->translateAttribute('name');
                    $title = SeoHelper::title($name);
                    $catDesc = $collection->translateAttribute('description', $locale) ?? '';
                    if ($catDesc) $description = \Illuminate\Support\Str::limit(strip_tags($catDesc), 160);
                }
            }
        }

        // Blog post
        if (preg_match('#^blog/(.+)$#', $path, $m) && $m[1] !== 'category') {
            $slug = $m[1];
            $post = \App\Models\BlogPost::where('slug', $slug)
                ->orWhere('slug_en', $slug)
                ->orWhere('slug_ru', $slug)
                ->first();
            if ($post) {
                $title = SeoHelper::title($post->t('meta_title', $locale) ?: $post->t('title', $locale));
                $description = $post->t('meta_description', $locale) ?: \Illuminate\Support\Str::limit(strip_tags($post->t('excerpt', $locale)), 160);
                $postImage = $post->getFirstMediaUrl('featured', 'large') ?: $post->getFirstMediaUrl('featured');
                if ($postImage) $image = $postImage;
                $type = 'article';
            }
        }

        $html = '<!DOCTYPE html><html><head>'
            . '<meta charset="utf-8">'
            . '<title>' . e($title) . '</title>'
            . '<meta name="description" content="' . e($description) . '">'
            . '<link rel="canonical" href="' . e($url) . '">'
            . '<meta property="og:type" content="' . $type . '">'
            . '<meta property="og:title" content="' . e($title) . '">'
            . '<meta property="og:description" content="' . e($description) . '">'
            . '<meta property="og:url" content="' . e($url) . '">'
            . '<meta property="og:image" content="' . e($image) . '">'
            . '<meta property="og:site_name" content="GN Industrial">'
            . '<meta name="twitter:card" content="summary_large_image">'
            . '<meta name="twitter:title" content="' . e($title) . '">'
            . '<meta name="twitter:description" content="' . e($description) . '">'
            . '<meta name="twitter:image" content="' . e($image) . '">'
            . '</head><body></body></html>';

        // Return raw response — bypass all remaining middleware (session, CSRF, cookies)
        // This prevents Laravel from adding cache-control:no-cache,private and session cookies
        // which trigger Cloudflare's JS challenge
        header('HTTP/1.1 200 OK');
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: public, max-age=3600');
        header('X-Robots-Tag: all');
        echo $html;
        exit;
    }
}
