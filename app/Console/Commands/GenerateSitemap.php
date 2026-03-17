<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Product;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate sitemap index with sub-sitemaps for products, categories, blog, and pages';

    private array $languages = ['ka', 'en', 'ru'];

    public function handle(): int
    {
        $this->info('Generating sitemaps...');

        $this->generatePagesSitemap();
        $this->generateProductsSitemap();
        $this->generateCategoriesSitemap();
        $this->generateBlogSitemap();
        $this->generateSitemapIndex();

        $this->info('All sitemaps generated.');
        return self::SUCCESS;
    }

    private function generatePagesSitemap(): void
    {
        $sitemap = Sitemap::create();
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

        $sitemap->writeToFile(public_path('pages-sitemap.xml'));
        $this->info("  Pages: " . count($pages) . " URLs");
    }

    private function generateProductsSitemap(): void
    {
        $products = Product::where('status', 'published')
            ->with(['urls.language'])
            ->get();

        $sitemap = Sitemap::create();

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

        $sitemap->writeToFile(public_path('products-sitemap.xml'));
        $this->info("  Products: {$products->count()} URLs");
    }

    private function generateCategoriesSitemap(): void
    {
        $group = CollectionGroup::where('handle', 'product-categories')->first();
        if (! $group) return;

        $collections = LunarCollection::where('collection_group_id', $group->id)
            ->has('products')
            ->with(['urls.language'])
            ->get();

        $sitemap = Sitemap::create();

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

        $sitemap->writeToFile(public_path('categories-sitemap.xml'));
        $this->info("  Categories: {$collections->count()} URLs");
    }

    private function generateBlogSitemap(): void
    {
        $posts = BlogPost::published()->get();
        $sitemap = Sitemap::create();

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

        $sitemap->writeToFile(public_path('blog-sitemap.xml'));
        $this->info("  Blog: {$posts->count()} URLs");
    }

    private function generateSitemapIndex(): void
    {
        $baseUrl = rtrim(config('app.url'), '/');

        SitemapIndex::create()
            ->add("{$baseUrl}/pages-sitemap.xml")
            ->add("{$baseUrl}/products-sitemap.xml")
            ->add("{$baseUrl}/categories-sitemap.xml")
            ->add("{$baseUrl}/blog-sitemap.xml")
            ->writeToFile(public_path('sitemap.xml'));

        $this->info("  Index: sitemap.xml → 4 sub-sitemaps");
    }

    private function url(string $prefix, string $path): string
    {
        return rtrim(config('app.url'), '/') . $prefix . $path;
    }
}
