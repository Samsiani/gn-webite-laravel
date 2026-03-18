<?php

namespace App\Console\Commands;

use App\Models\BlogCategory;
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
    protected $description = 'Generate sitemap index with sub-sitemaps (Rank Math pattern)';

    private array $languages = ['ka', 'en', 'ru'];
    private int $perFile = 1000;

    public function handle(): int
    {
        $this->info('Generating sitemaps...');

        $index = SitemapIndex::create();

        $this->generatePagesSitemap($index);
        $this->generateProductSitemaps($index);
        $this->generateCategoriesSitemap($index);
        $this->generateBlogSitemap($index);
        $this->generateBlogCategoriesSitemap($index);

        $index->writeToFile(public_path('sitemap.xml'));
        $this->info('Sitemap index generated → sitemap.xml');

        return self::SUCCESS;
    }

    private function generatePagesSitemap(SitemapIndex $index): void
    {
        $sitemap = Sitemap::create();
        $pages = ['/', '/shop', '/blog', '/contact'];

        foreach ($pages as $page) {
            $url = Url::create($this->url('', $page))
                ->setChangeFrequency($page === '/' ? Url::CHANGE_FREQUENCY_DAILY : Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority($page === '/' ? 1.0 : 0.7);

            foreach ($this->languages as $lang) {
                $url->addAlternate($this->url($lang === 'ka' ? '' : '/' . $lang, $page), $lang);
            }
            $sitemap->add($url);
        }

        $path = 'page-sitemap.xml';
        $sitemap->writeToFile(public_path($path));
        $index->add($this->baseUrl() . '/' . $path);
        $this->info("  Pages: " . count($pages) . " URLs → {$path}");
    }

    private function generateProductSitemaps(SitemapIndex $index): void
    {
        $products = Product::where('status', 'published')
            ->with(['urls.language'])
            ->orderBy('id')
            ->get();

        $chunks = $products->chunk($this->perFile);
        $total = $products->count();
        $fileNum = 0;

        foreach ($chunks as $chunk) {
            $fileNum++;
            $sitemap = Sitemap::create();

            foreach ($chunk as $product) {
                $urls = [];
                foreach ($product->urls as $u) {
                    $lang = $u->language?->code;
                    if ($lang) {
                        $urls[$lang] = $this->url($lang === 'ka' ? '' : '/' . $lang, '/product/' . $u->slug);
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

            $path = "product-sitemap{$fileNum}.xml";
            $sitemap->writeToFile(public_path($path));
            $index->add($this->baseUrl() . '/' . $path);
        }

        $this->info("  Products: {$total} URLs → {$fileNum} file(s)");
    }

    private function generateCategoriesSitemap(SitemapIndex $index): void
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
                    $urls[$lang] = $this->url($lang === 'ka' ? '' : '/' . $lang, '/category/' . $u->slug);
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

        $path = 'product_cat-sitemap.xml';
        $sitemap->writeToFile(public_path($path));
        $index->add($this->baseUrl() . '/' . $path);
        $this->info("  Product categories: {$collections->count()} URLs → {$path}");
    }

    private function generateBlogSitemap(SitemapIndex $index): void
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

        $path = 'post-sitemap.xml';
        $sitemap->writeToFile(public_path($path));
        $index->add($this->baseUrl() . '/' . $path);
        $this->info("  Blog posts: {$posts->count()} URLs → {$path}");
    }

    private function generateBlogCategoriesSitemap(SitemapIndex $index): void
    {
        $categories = BlogCategory::withCount(['posts' => fn ($q) => $q->published()])
            ->having('posts_count', '>', 0)
            ->get();

        if ($categories->isEmpty()) return;

        $sitemap = Sitemap::create();

        foreach ($categories as $cat) {
            $urls = ['ka' => $this->url('', '/blog/category/' . $cat->slug)];
            if ($cat->slug_en) $urls['en'] = $this->url('/en', '/blog/category/' . $cat->slug_en);
            if ($cat->slug_ru) $urls['ru'] = $this->url('/ru', '/blog/category/' . $cat->slug_ru);

            $url = Url::create($urls['ka'])
                ->setLastModificationDate($cat->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.5);

            foreach ($urls as $lang => $href) {
                $url->addAlternate($href, $lang);
            }
            $sitemap->add($url);
        }

        $path = 'category-sitemap.xml';
        $sitemap->writeToFile(public_path($path));
        $index->add($this->baseUrl() . '/' . $path);
        $this->info("  Blog categories: {$categories->count()} URLs → {$path}");
    }

    private function url(string $prefix, string $path): string
    {
        return $this->baseUrl() . $prefix . $path;
    }

    private function baseUrl(): string
    {
        return rtrim(config('app.url'), '/');
    }
}
