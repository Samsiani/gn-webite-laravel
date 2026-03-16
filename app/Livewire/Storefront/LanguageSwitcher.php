<?php

namespace App\Livewire\Storefront;

use App\Models\BlogPost;
use Livewire\Component;
use Lunar\Models\Url;

class LanguageSwitcher extends Component
{
    public function render()
    {
        $locale = app()->getLocale();
        $links = $this->buildLinks();

        return view('livewire.storefront.language-switcher', [
            'locale' => $locale,
            'links' => $links,
        ]);
    }

    private function buildLinks(): array
    {
        $request = request();
        $segments = $request->segments();
        $currentLocale = app()->getLocale();

        // Detect current page type from route
        $route = $request->route();
        $routeName = $route?->getName() ?? '';

        // Strip locale prefix from route name (e.g., "en.product.show" → "product.show")
        $baseRouteName = preg_replace('/^(en\.|ru\.)/', '', $routeName);

        $links = [
            'ka' => '/',
            'en' => '/en',
            'ru' => '/ru',
        ];

        // Product page — find translated slugs
        if ($baseRouteName === 'product.show') {
            $slug = $route->parameter('slug');
            $productUrl = Url::where('slug', $slug)->where('element_type', 'product')->first();

            if ($productUrl) {
                $allUrls = Url::where('element_type', 'product')
                    ->where('element_id', $productUrl->element_id)
                    ->with('language')
                    ->get();

                foreach ($allUrls as $url) {
                    $lang = $url->language?->code;
                    if ($lang === 'ka') {
                        $links['ka'] = '/product/' . $url->slug;
                    } elseif ($lang === 'en') {
                        $links['en'] = '/en/product/' . $url->slug;
                    } elseif ($lang === 'ru') {
                        $links['ru'] = '/ru/product/' . $url->slug;
                    }
                }
            }
        }

        // Category page — find translated slugs
        if ($baseRouteName === 'category.show') {
            $slug = $route->parameter('slug');
            $collectionUrl = Url::where('slug', $slug)->where('element_type', 'collection')->first();

            if ($collectionUrl) {
                $allUrls = Url::where('element_type', 'collection')
                    ->where('element_id', $collectionUrl->element_id)
                    ->with('language')
                    ->get();

                foreach ($allUrls as $url) {
                    $lang = $url->language?->code;
                    if ($lang === 'ka') {
                        $links['ka'] = '/category/' . $url->slug;
                    } elseif ($lang === 'en') {
                        $links['en'] = '/en/category/' . $url->slug;
                    } elseif ($lang === 'ru') {
                        $links['ru'] = '/ru/category/' . $url->slug;
                    }
                }
            }
        }

        // Blog post — find translated slugs
        if ($baseRouteName === 'blog.show') {
            $slug = $route->parameter('slug');
            $post = BlogPost::where('slug', $slug)
                ->orWhere('slug_en', $slug)
                ->orWhere('slug_ru', $slug)
                ->first();

            if ($post) {
                $links['ka'] = '/blog/' . $post->slug;
                if ($post->slug_en) $links['en'] = '/en/blog/' . $post->slug_en;
                if ($post->slug_ru) $links['ru'] = '/ru/blog/' . $post->slug_ru;
            }
        }

        // Blog category
        if ($baseRouteName === 'blog.category') {
            $catSlug = $route->parameter('category');
            $cat = \App\Models\BlogCategory::where('slug', $catSlug)
                ->orWhere('slug_en', $catSlug)
                ->orWhere('slug_ru', $catSlug)
                ->first();

            if ($cat) {
                $links['ka'] = '/blog/category/' . $cat->slug;
                $links['en'] = '/en/blog/category/' . ($cat->slug_en ?: $cat->slug);
                $links['ru'] = '/ru/blog/category/' . ($cat->slug_ru ?: $cat->slug);
            }
        }

        // Static pages (shop, cart, checkout, search, blog) — just swap prefix
        if (in_array($baseRouteName, ['shop', 'cart', 'checkout', 'search', 'blog'])) {
            $links['ka'] = '/' . $baseRouteName;
            $links['en'] = '/en/' . $baseRouteName;
            $links['ru'] = '/ru/' . $baseRouteName;
        }

        return $links;
    }
}
