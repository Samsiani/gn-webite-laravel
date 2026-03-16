<?php

namespace App\Console\Commands;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportBlogPosts extends Command
{
    protected $signature = 'gn:import-blog {--limit=10 : Number of posts to import}';
    protected $description = 'Import blog posts from gn.ge with translations and product blocks';

    private string $baseUrl;

    public function handle(): int
    {
        $this->baseUrl = rtrim(config('services.woocommerce.url', 'https://gn.ge'), '/');
        $limit = (int) $this->option('limit');

        $this->info("Fetching last {$limit} posts from {$this->baseUrl}...");
        $this->importCategories();

        // Fetch all 3 languages
        $kaPosts = $this->wpGet('posts', ['per_page' => $limit, 'orderby' => 'date', 'order' => 'desc', '_embed' => 1]) ?? collect();
        $enPosts = $this->wpGet('posts', ['per_page' => $limit, 'orderby' => 'date', 'order' => 'desc', 'lang' => 'en', '_embed' => 1]) ?? collect();
        $ruPosts = $this->wpGet('posts', ['per_page' => $limit, 'orderby' => 'date', 'order' => 'desc', 'lang' => 'ru', '_embed' => 1]) ?? collect();

        $this->info("Found: KA={$kaPosts->count()}, EN={$enPosts->count()}, RU={$ruPosts->count()}");

        foreach ($kaPosts as $i => $ka) {
            $title = html_entity_decode(strip_tags($ka['title']['rendered'] ?? ''));
            $slug = Str::slug(urldecode($ka['slug'])) ?: Str::slug($title);

            if (BlogPost::where('slug', $slug)->exists()) {
                $this->line("  ⊘ {$title} (exists)");
                continue;
            }

            $en = $enPosts[$i] ?? null;
            $ru = $ruPosts[$i] ?? null;

            // Convert HTML content to blocks
            $kaContent = $ka['content']['rendered'] ?? '';
            $enContent = $en['content']['rendered'] ?? '';
            $ruContent = $ru['content']['rendered'] ?? '';

            $post = BlogPost::create([
                'title' => $title,
                'title_en' => $en ? html_entity_decode(strip_tags($en['title']['rendered'] ?? '')) : null,
                'title_ru' => $ru ? html_entity_decode(strip_tags($ru['title']['rendered'] ?? '')) : null,
                'slug' => $slug,
                'slug_en' => $en ? (Str::slug(urldecode($en['slug'] ?? '')) ?: null) : null,
                'slug_ru' => $ru ? (Str::slug(urldecode($ru['slug'] ?? '')) ?: null) : null,
                'excerpt' => $ka['excerpt']['rendered'] ?? '',
                'excerpt_en' => $en['excerpt']['rendered'] ?? null,
                'excerpt_ru' => $ru['excerpt']['rendered'] ?? null,
                'content' => $kaContent,
                'content_en' => $enContent,
                'content_ru' => $ruContent,
                'blocks' => $this->htmlToBlocks($kaContent),
                'blocks_en' => $en ? $this->htmlToBlocks($enContent) : null,
                'blocks_ru' => $ru ? $this->htmlToBlocks($ruContent) : null,
                'status' => 'published',
                'published_at' => $ka['date'] ?? now(),
                'blog_category_id' => $this->mapCategory($ka['categories'] ?? []),
            ]);

            // Featured image
            $imageUrl = $this->getFeaturedImageUrl($ka);
            if ($imageUrl) {
                try {
                    $post->addMediaFromUrl($imageUrl)->toMediaCollection('featured');
                } catch (\Exception $e) {
                    // Fallback for Georgian filenames
                    try {
                        $r = Http::withHeaders(['User-Agent' => 'GN-Import/1.0'])->timeout(30)->get($imageUrl);
                        if ($r->ok()) {
                            $fname = Str::slug(pathinfo(urldecode($imageUrl), PATHINFO_FILENAME)) . '.' . pathinfo($imageUrl, PATHINFO_EXTENSION);
                            $post->addMediaFromString($r->body())->usingFileName($fname ?: 'featured.jpg')->toMediaCollection('featured');
                        }
                    } catch (\Exception $e2) {}
                }
            }

            $blockCount = count($post->blocks ?? []);
            $productCount = collect($post->blocks ?? [])->where('type', 'product')->count();
            $this->line("  ✓ {$title} ({$blockCount} blocks, {$productCount} products)");
            if ($en) $this->line("    EN: " . html_entity_decode(strip_tags($en['title']['rendered'] ?? '')));
            if ($ru) $this->line("    RU: " . html_entity_decode(strip_tags($ru['title']['rendered'] ?? '')));
        }

        $this->info('Import complete! Total: ' . BlogPost::count() . ' posts');
        return self::SUCCESS;
    }

    /**
     * Convert HTML content to blocks, extracting product links as product blocks.
     */
    private function htmlToBlocks(string $html): array
    {
        if (! $html || ! trim(strip_tags($html))) return [];

        $blocks = [];

        // Find all product links and their SKUs
        $productSlugs = [];
        // 1. Extract product SKUs from two sources:
        //    a) WooCommerce Gutenberg product-collection blocks (data-query with HandPickedProducts)
        //    b) Direct product links (<a href="/product/slug">)

        $allSkus = [];

        // a) Extract WC product IDs from Gutenberg blocks
        preg_match_all('#woocommerceHandPickedProducts["\s:]*\[([^\]]+)\]#i', $html, $wcBlocks);
        $wcProductIds = [];
        foreach ($wcBlocks[1] ?? [] as $idList) {
            preg_match_all('#"(\d+)"#', $idList, $ids);
            $wcProductIds = array_merge($wcProductIds, $ids[1] ?? []);
        }

        // Resolve WC product IDs to SKUs via wp_id_map
        foreach (array_unique($wcProductIds) as $wcId) {
            $map = \Illuminate\Support\Facades\DB::table('wp_id_map')
                ->where('entity_type', 'product')->where('wp_id', $wcId)->first();
            if ($map) {
                $variant = \Lunar\Models\ProductVariant::where('product_id', $map->lunar_id)->first();
                if ($variant && ! in_array($variant->sku, $allSkus)) {
                    $allSkus[] = $variant->sku;
                }
            }
        }

        // b) Extract product slugs from href links
        preg_match_all('#href=["\'][^"\']*?/product/([^"\'<\s/]+)/?["\']#i', $html, $linkMatches);
        foreach ($linkMatches[1] ?? [] as $rawSlug) {
            $cleanSlug = Str::slug(urldecode(rtrim($rawSlug, '/')));
            $url = \Lunar\Models\Url::where('element_type', 'product')->where('slug', $cleanSlug)->first();
            if ($url) {
                $variant = \Lunar\Models\ProductVariant::where('product_id', $url->element_id)->first();
                if ($variant && ! in_array($variant->sku, $allSkus)) {
                    $allSkus[] = $variant->sku;
                }
            }
        }

        // 2. Split HTML by WC product-collection blocks — keep text between them in order
        // Remove entire WC product-collection blocks
        $cleaned = preg_replace(
            '#<div[^>]*data-block-name="woocommerce/product-collection"[^>]*>.*?</div>\s*</div>\s*</div>#is',
            '<!-- PRODUCT_BLOCK_PLACEHOLDER -->',
            $html
        );
        // Also try simpler pattern for the whole block wrapper
        $cleaned = preg_replace(
            '#<div[^>]*class="[^"]*wp-block-woocommerce-product-collection[^"]*"[^>]*>.*?</ul>\s*</div>#is',
            '<!-- PRODUCT_BLOCK_PLACEHOLDER -->',
            $cleaned
        );

        // Remove standalone product links (not inside WC blocks)
        $cleaned = preg_replace(
            '#<a[^>]*href=["\'][^"\']*?/product/[^"\']+["\'][^>]*>.*?</a>#is',
            '',
            $cleaned
        );

        // Clean up empty elements and orphaned prices
        $cleaned = preg_replace('#<(p|div|figure|li|ul|span|h2)[^>]*>\s*</\1>#is', '', $cleaned);
        $cleaned = preg_replace('#<(p|div|figure|li|ul|span|h2)[^>]*>\s*</\1>#is', '', $cleaned);
        $cleaned = preg_replace('~[\d\s,.]+&nbsp;&\#x20be;~', '', $cleaned);
        $cleaned = preg_replace('~[\d\s,.]+₾~u', '', $cleaned);

        if (empty($allSkus)) {
            $text = trim($cleaned);
            return ($text && trim(strip_tags($text))) ? [['type' => 'text', 'data' => ['content' => $text]]] : [];
        }

        // 3. Build blocks — split by placeholder, insert product block in place
        $productBlock = ['type' => 'products', 'data' => [
            'source' => 'manual',
            'skus' => implode(', ', $allSkus),
            'limit' => count($allSkus),
            'columns' => min(count($allSkus), 4),
        ]];

        $parts = preg_split('#<!-- PRODUCT_BLOCK_PLACEHOLDER -->#', $cleaned);
        $productInserted = false;

        foreach ($parts as $part) {
            $text = trim($part);
            if ($text && trim(strip_tags($text))) {
                $blocks[] = ['type' => 'text', 'data' => ['content' => $text]];
            }
            // Insert product block after the first text section where placeholder was
            if (! $productInserted && count($parts) > 1) {
                $blocks[] = $productBlock;
                $productInserted = true;
            }
        }

        // If no placeholder found, append products at end
        if (! $productInserted) {
            $blocks[] = $productBlock;
        }

        return $blocks;
    }

    private function importCategories(): void
    {
        $cats = $this->wpGet('categories', ['per_page' => 100]);
        if (! $cats) return;
        foreach ($cats as $cat) {
            if (($cat['count'] ?? 0) === 0) continue;
            BlogCategory::firstOrCreate(
                ['slug' => Str::slug(urldecode($cat['slug'])) ?: 'blog'],
                ['name' => html_entity_decode($cat['name']), 'position' => 0]
            );
        }
    }

    private function mapCategory(array $wpCatIds): ?int
    {
        if (empty($wpCatIds)) return null;
        $cat = $this->wpGet("categories/{$wpCatIds[0]}");
        if (! $cat || empty($cat['name'])) return null;
        return BlogCategory::where('name', html_entity_decode($cat['name']))->first()?->id;
    }

    private function getFeaturedImageUrl(array $wp): ?string
    {
        $embedded = $wp['_embedded']['wp:featuredmedia'][0] ?? null;
        if ($embedded && ! empty($embedded['source_url'])) return $embedded['source_url'];
        $mediaId = $wp['featured_media'] ?? 0;
        if (! $mediaId) return null;
        $media = $this->wpGet("media/{$mediaId}");
        return $media['source_url'] ?? null;
    }

    private function wpGet(string $endpoint, array $params = []): mixed
    {
        try {
            $r = Http::withHeaders(['User-Agent' => 'GN-Import/1.0'])
                ->timeout(30)
                ->get("{$this->baseUrl}/wp-json/wp/v2/{$endpoint}", $params);
            return $r->ok() ? collect($r->json()) : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
