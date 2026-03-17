<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Product;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate XML sitemap with products, categories, and blog posts in all languages';

    private array $languages = ['ka', 'en', 'ru'];

    public function handle(): int
    {
        $this->info('Generating sitemap...');
        $sitemap = Sitemap::create();

        $this->addStaticPages($sitemap);
        $this->addProducts($sitemap);
        $this->addCategories($sitemap);
        $this->addBlogPosts($sitemap);

        $path = public_path('sitemap.xml');
        $sitemap->writeToFile($path);

        $count = substr_count(file_get_contents($path), '<url>');
        $this->info("Sitemap generated: {$count} URLs → {$path}");

        return self::SUCCESS;
    }

    private function addStaticPages(Sitemap $sitemap): void
    {
        $pages = ['/', '/shop', '/blog', '/contact'];

        foreach ($pages as $page) {
            $url = Url::create($this->url('', $page))
                ->setChangeFrequency($page === '/' ? Url::CHANGE_FREQUENCY_DAILY : Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority($page === '/' ? 1.0 : 0.7);

            foreach ($this->languages as $lang) {
                $prefix = $lang === 'ka' ? '' : '/' . $lang;
                $url->addAlternate($this->url($prefix, $page), $lang);
            }

            $sitemap->add($url);
        }
    }

    private function addProducts(Sitemap $sitemap): void
    {
        $products = Product::where('status', 'published')
            ->with(['urls.language'])
            ->get();

        $this->info("  Products: {$products->count()}");

        foreach ($products as $product) {
            $urls = [];
            foreach ($product->urls as $u) {
                $lang = $u->language?->code;
                if ($lang) {
                    $prefix = $lang === 'ka' ? '' : '/' . $lang;
                    $urls[$lang] = $this->url($prefix, '/product/' . $u->slug);
                }
            }

            $primary = $urls['ka'] ?? $urls['en'] ?? reset($urls);
            if (! $primary) continue;

            $url = Url::create($primary)
                ->setLastModificationDate($product->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.8);

            foreach ($urls as $lang => $href) {
                $url->addAlternate($href, $lang);
            }

            $sitemap->add($url);
        }
    }

    private function addCategories(Sitemap $sitemap): void
    {
        $group = CollectionGroup::where('handle', 'product-categories')->first();
        if (! $group) return;

        $collections = LunarCollection::where('collection_group_id', $group->id)
            ->has('products')
            ->with(['urls.language'])
            ->get();

        $this->info("  Categories: {$collections->count()}");

        foreach ($collections as $collection) {
            $urls = [];
            foreach ($collection->urls as $u) {
                $lang = $u->language?->code;
                if ($lang) {
                    $prefix = $lang === 'ka' ? '' : '/' . $lang;
                    $urls[$lang] = $this->url($prefix, '/category/' . $u->slug);
                }
            }

            $primary = $urls['ka'] ?? $urls['en'] ?? reset($urls);
            if (! $primary) continue;

            $url = Url::create($primary)
                ->setLastModificationDate($collection->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.7);

            foreach ($urls as $lang => $href) {
                $url->addAlternate($href, $lang);
            }

            $sitemap->add($url);
        }
    }

    private function addBlogPosts(Sitemap $sitemap): void
    {
        $posts = BlogPost::published()->get();

        $this->info("  Blog posts: {$posts->count()}");

        foreach ($posts as $post) {
            $urls = ['ka' => $this->url('', '/blog/' . $post->slug)];
            if ($post->slug_en) $urls['en'] = $this->url('/en', '/blog/' . $post->slug_en);
            if ($post->slug_ru) $urls['ru'] = $this->url('/ru', '/blog/' . $post->slug_ru);

            $url = Url::create($urls['ka'])
                ->setLastModificationDate($post->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.6);

            foreach ($urls as $lang => $href) {
                $url->addAlternate($href, $lang);
            }

            $sitemap->add($url);
        }
    }

    private function url(string $prefix, string $path): string
    {
        return rtrim(config('app.url'), '/') . $prefix . $path;
    }
}
