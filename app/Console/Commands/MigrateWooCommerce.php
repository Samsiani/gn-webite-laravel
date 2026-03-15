<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Attribute;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;
use Lunar\Models\Url;

class MigrateWooCommerce extends Command
{
    protected $signature = 'gn:migrate-woocommerce
                            {--step= : Run a specific step (collections, products, media, redirects)}
                            {--limit= : Limit number of items to process}
                            {--offset=0 : Offset for pagination}';

    protected $description = 'Migrate WooCommerce data from gn.ge to Lunar';

    private string $prefix;
    private array $languages;
    private ?Currency $currency;
    private ?ProductType $productType;
    private ?CollectionGroup $collectionGroup;

    public function handle(): int
    {
        $this->prefix = config('database.connections.wordpress.prefix', 'wp_');

        if (! $this->checkConnection()) {
            return self::FAILURE;
        }

        $this->languages = Language::all()->pluck('id', 'code')->toArray();
        $this->currency = Currency::where('code', 'GEL')->first();
        $this->productType = ProductType::first();
        $this->collectionGroup = CollectionGroup::where('handle', 'product-categories')->first();

        $step = $this->option('step');

        if ($step) {
            return match ($step) {
                'collections' => $this->migrateCollections(),
                'products' => $this->migrateProducts(),
                'media' => $this->migrateMedia(),
                'redirects' => $this->generateRedirects(),
                default => $this->error("Unknown step: {$step}") ?? self::FAILURE,
            };
        }

        $this->migrateCollections();
        $this->migrateProducts();
        $this->generateRedirects();

        $this->info('Migration complete!');
        return self::SUCCESS;
    }

    private function checkConnection(): bool
    {
        try {
            $count = DB::connection('wordpress')
                ->table('posts')
                ->where('post_type', 'product')
                ->where('post_status', 'publish')
                ->count();

            $this->info("Connected to WordPress DB. Found {$count} published products.");
            return true;
        } catch (\Exception $e) {
            $this->error('Cannot connect to WordPress database: ' . $e->getMessage());
            $this->line('Configure WP_DB_* variables in .env');
            return false;
        }
    }

    // -------------------------------------------------------
    // STEP 1: Collections (Categories)
    // -------------------------------------------------------
    private function migrateCollections(): int
    {
        $this->info('--- Migrating Categories → Collections ---');

        $wp = DB::connection('wordpress');

        // Get all product categories with their hierarchy
        $categories = $wp->table('term_taxonomy as tt')
            ->join('terms as t', 't.term_id', '=', 'tt.term_id')
            ->where('tt.taxonomy', 'product_cat')
            ->select('t.term_id', 't.name', 't.slug', 'tt.parent', 'tt.description', 'tt.count')
            ->orderBy('tt.parent')
            ->orderBy('t.name')
            ->get();

        $this->info("Found {$categories->count()} categories.");

        $bar = $this->output->createProgressBar($categories->count());

        foreach ($categories as $cat) {
            // Get translations for this category via WPML
            $translations = $this->getTermTranslations($cat->term_id, 'tax_product_cat');

            // Build translated name
            $translatedName = $this->buildTranslatedField($translations, 'name', $cat->name);
            $translatedSlug = $this->buildTranslatedField($translations, 'slug', $cat->slug);

            // Check if already migrated
            $existing = DB::table('wp_id_map')
                ->where('entity_type', 'category')
                ->where('wp_id', $cat->term_id)
                ->first();

            if ($existing) {
                $bar->advance();
                continue;
            }

            // Find parent collection if exists
            $parentId = null;
            if ($cat->parent > 0) {
                $parentMap = DB::table('wp_id_map')
                    ->where('entity_type', 'category')
                    ->where('wp_id', $cat->parent)
                    ->first();
                $parentId = $parentMap?->lunar_id;
            }

            // Create Lunar collection
            $collection = LunarCollection::create([
                'collection_group_id' => $this->collectionGroup->id,
                'attribute_data' => [
                    'name' => new TranslatedText(collect($translatedName)->map(
                        fn ($v) => new Text($v)
                    )),
                ],
            ]);

            // Set parent if applicable (nested set)
            if ($parentId) {
                $parent = LunarCollection::find($parentId);
                if ($parent) {
                    $collection->appendToNode($parent)->save();
                }
            }

            // Create URLs for each language
            foreach ($translatedSlug as $lang => $slug) {
                if (! $slug || ! isset($this->languages[$lang])) {
                    continue;
                }

                Url::create([
                    'element_type' => LunarCollection::class,
                    'element_id' => $collection->id,
                    'language_id' => $this->languages[$lang],
                    'slug' => Str::slug($slug),
                    'default' => $lang === 'ka',
                ]);
            }

            // Save mapping
            DB::table('wp_id_map')->insert([
                'entity_type' => 'category',
                'wp_id' => $cat->term_id,
                'lunar_id' => $collection->id,
                'lang' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Collections migrated: ' . $categories->count());

        return self::SUCCESS;
    }

    // -------------------------------------------------------
    // STEP 2: Products
    // -------------------------------------------------------
    private function migrateProducts(): int
    {
        $this->info('--- Migrating Products ---');

        $wp = DB::connection('wordpress');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $offset = (int) $this->option('offset');

        // Get all unique translation groups (trids) for products
        $query = $wp->table('icl_translations')
            ->where('element_type', 'post_product')
            ->select('trid')
            ->distinct()
            ->orderBy('trid');

        if ($limit) {
            $query->limit($limit)->offset($offset);
        }

        $trids = $query->pluck('trid');
        $this->info("Found {$trids->count()} unique products to migrate.");

        $bar = $this->output->createProgressBar($trids->count());
        $migrated = 0;
        $skipped = 0;

        foreach ($trids->chunk(100) as $chunk) {
            foreach ($chunk as $trid) {
                try {
                    if ($this->migrateProduct($trid)) {
                        $migrated++;
                    } else {
                        $skipped++;
                    }
                } catch (\Exception $e) {
                    $this->newLine();
                    $this->warn("Error migrating trid {$trid}: {$e->getMessage()}");
                    $skipped++;
                }
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("Products migrated: {$migrated}, skipped: {$skipped}");

        return self::SUCCESS;
    }

    private function migrateProduct(int $trid): bool
    {
        $wp = DB::connection('wordpress');

        // Check if already migrated
        $existingMap = DB::table('wp_id_map')
            ->where('entity_type', 'product')
            ->whereIn('wp_id', function ($q) use ($wp, $trid) {
                $q->select('element_id')
                    ->from($wp->getTablePrefix() . 'icl_translations')
                    ->where('trid', $trid)
                    ->where('element_type', 'post_product');
            })
            ->first();

        if ($existingMap) {
            return false;
        }

        // Get all translations for this trid
        $translations = $wp->table('icl_translations')
            ->where('trid', $trid)
            ->where('element_type', 'post_product')
            ->get()
            ->keyBy('language_code');

        // Fetch post data for each language
        $posts = [];
        $metas = [];
        foreach ($translations as $lang => $trans) {
            $post = $wp->table('posts')
                ->where('ID', $trans->element_id)
                ->where('post_status', 'publish')
                ->first();

            if ($post) {
                $posts[$lang] = $post;
                $metas[$lang] = $wp->table('postmeta')
                    ->where('post_id', $post->ID)
                    ->pluck('meta_value', 'meta_key')
                    ->toArray();
            }
        }

        if (empty($posts)) {
            return false;
        }

        // Use the default language (ka) post as primary, fall back to first available
        $primaryLang = isset($posts['ka']) ? 'ka' : array_key_first($posts);
        $primaryPost = $posts[$primaryLang];
        $primaryMeta = $metas[$primaryLang];

        // Build translated attribute data
        $attributeData = $this->buildProductAttributes($posts, $metas);

        // Create Lunar product
        $product = Product::create([
            'product_type_id' => $this->productType->id,
            'status' => 'published',
            'attribute_data' => $attributeData,
        ]);

        // Create variant
        $sku = $primaryMeta['_sku'] ?? 'GN-' . $primaryPost->ID;
        $stock = (int) ($primaryMeta['_stock'] ?? 0);
        $stockStatus = $primaryMeta['_stock_status'] ?? 'instock';

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'tax_class_id' => 1,
            'sku' => $sku,
            'stock' => $stock,
            'purchasable' => $stockStatus === 'instock' ? 'always' : 'in_stock',
        ]);

        // Create price
        $regularPrice = (float) ($primaryMeta['_regular_price'] ?? $primaryMeta['_price'] ?? 0);
        $salePrice = ! empty($primaryMeta['_sale_price']) ? (float) $primaryMeta['_sale_price'] : null;

        if ($regularPrice > 0) {
            Price::create([
                'priceable_type' => ProductVariant::class,
                'priceable_id' => $variant->id,
                'currency_id' => $this->currency->id,
                'price' => (int) round($regularPrice * 100),
                'compare_price' => $salePrice ? (int) round($salePrice * 100) : null,
                'min_quantity' => 1,
            ]);
        }

        // Link to collections (categories)
        $categoryIds = $wp->table('term_relationships as tr')
            ->join('term_taxonomy as tt', 'tt.term_taxonomy_id', '=', 'tr.term_taxonomy_id')
            ->where('tr.object_id', $primaryPost->ID)
            ->where('tt.taxonomy', 'product_cat')
            ->pluck('tt.term_id');

        foreach ($categoryIds as $wpCatId) {
            $catMap = DB::table('wp_id_map')
                ->where('entity_type', 'category')
                ->where('wp_id', $wpCatId)
                ->first();

            if ($catMap) {
                DB::table('lunar_collection_product')->insertOrIgnore([
                    'collection_id' => $catMap->lunar_id,
                    'product_id' => $product->id,
                    'position' => 0,
                ]);
            }
        }

        // Create URLs for each language
        foreach ($posts as $lang => $post) {
            if (! isset($this->languages[$lang])) {
                continue;
            }

            Url::create([
                'element_type' => Product::class,
                'element_id' => $product->id,
                'language_id' => $this->languages[$lang],
                'slug' => $post->post_name,
                'default' => $lang === 'ka',
            ]);
        }

        // Save mappings for all language posts
        foreach ($posts as $lang => $post) {
            DB::table('wp_id_map')->insert([
                'entity_type' => 'product',
                'wp_id' => $post->ID,
                'lunar_id' => $product->id,
                'lang' => $lang,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return true;
    }

    private function buildProductAttributes(array $posts, array $metas): array
    {
        $wp = DB::connection('wordpress');

        // Build translated text fields
        $names = [];
        $descriptions = [];
        $shortDescriptions = [];

        foreach ($posts as $lang => $post) {
            $names[$lang] = new Text($post->post_title);
            $descriptions[$lang] = new Text($post->post_content ?: '');
            $shortDescriptions[$lang] = new Text($post->post_excerpt ?: '');
        }

        $data = [
            'name' => new TranslatedText(collect($names)),
            'description' => new TranslatedText(collect($descriptions)),
            'short_description' => new TranslatedText(collect($shortDescriptions)),
        ];

        // Primary meta (from ka or first available)
        $primaryLang = isset($metas['ka']) ? 'ka' : array_key_first($metas);
        $meta = $metas[$primaryLang];

        // Map WC product attributes to Lunar attributes
        $primaryPost = $posts[$primaryLang];
        $terms = $wp->table('term_relationships as tr')
            ->join('term_taxonomy as tt', 'tt.term_taxonomy_id', '=', 'tr.term_taxonomy_id')
            ->join('terms as t', 't.term_id', '=', 'tt.term_id')
            ->where('tr.object_id', $primaryPost->ID)
            ->whereIn('tt.taxonomy', ['pa_prod-brand', 'pa_kontroli', 'pa_korpusi', 'pa_kvebis-tskharo', 'pa_gazis-mokhmareba'])
            ->select('tt.taxonomy', 't.name')
            ->get();

        $attrMap = [
            'pa_prod-brand' => 'brand',
            'pa_kontroli' => 'control_type',
            'pa_korpusi' => 'body_material',
            'pa_kvebis-tskharo' => 'power_source',
            'pa_gazis-mokhmareba' => 'gas_consumption',
        ];

        foreach ($terms as $term) {
            $handle = $attrMap[$term->taxonomy] ?? null;
            if ($handle) {
                $data[$handle] = new Text($term->name);
            }
        }

        // Technical details from postmeta
        $techMap = [
            '_power' => 'power',
            '_voltage' => 'voltage',
            '_dimensions' => 'dimensions',
            '_weight' => 'weight',
            '_capacity' => 'capacity',
        ];

        foreach ($techMap as $metaKey => $handle) {
            if (! empty($meta[$metaKey])) {
                $data[$handle] = new Text($meta[$metaKey]);
            }
        }

        // SEO meta from Rank Math
        $seoTitles = [];
        $seoDescriptions = [];
        foreach ($metas as $lang => $m) {
            if (! empty($m['rank_math_title'])) {
                $seoTitles[$lang] = new Text($m['rank_math_title']);
            }
            if (! empty($m['rank_math_description'])) {
                $seoDescriptions[$lang] = new Text($m['rank_math_description']);
            }
        }

        if (! empty($seoTitles)) {
            $data['meta_title'] = new TranslatedText(collect($seoTitles));
        }
        if (! empty($seoDescriptions)) {
            $data['meta_description'] = new TranslatedText(collect($seoDescriptions));
        }

        return $data;
    }

    // -------------------------------------------------------
    // STEP 3: Media (images)
    // -------------------------------------------------------
    private function migrateMedia(): int
    {
        $this->info('--- Migrating Product Images ---');

        $wp = DB::connection('wordpress');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        $query = DB::table('wp_id_map')
            ->where('entity_type', 'product')
            ->whereNotNull('lang')
            ->where('lang', 'ka'); // Only process once per product

        if ($limit) {
            $query->limit($limit);
        }

        $maps = $query->get();
        $this->info("Processing images for {$maps->count()} products.");

        $bar = $this->output->createProgressBar($maps->count());

        foreach ($maps as $map) {
            $product = Product::find($map->lunar_id);
            if (! $product || $product->getMedia('images')->count() > 0) {
                $bar->advance();
                continue;
            }

            // Get featured image
            $thumbnailId = $wp->table('postmeta')
                ->where('post_id', $map->wp_id)
                ->where('meta_key', '_thumbnail_id')
                ->value('meta_value');

            // Get gallery images
            $galleryIds = $wp->table('postmeta')
                ->where('post_id', $map->wp_id)
                ->where('meta_key', '_product_image_gallery')
                ->value('meta_value');

            $imageIds = collect();
            if ($thumbnailId) {
                $imageIds->push($thumbnailId);
            }
            if ($galleryIds) {
                $imageIds = $imageIds->merge(explode(',', $galleryIds));
            }

            foreach ($imageIds->unique() as $imageId) {
                $imageUrl = $wp->table('posts')
                    ->where('ID', $imageId)
                    ->value('guid');

                if (! $imageUrl) {
                    continue;
                }

                try {
                    $product->addMediaFromUrl($imageUrl)
                        ->toMediaCollection('images');
                } catch (\Exception $e) {
                    $this->newLine();
                    $this->warn("Failed to download image {$imageUrl}: {$e->getMessage()}");
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Media migration complete.');

        return self::SUCCESS;
    }

    // -------------------------------------------------------
    // STEP 4: Generate Redirects
    // -------------------------------------------------------
    private function generateRedirects(): int
    {
        $this->info('--- Generating Redirect Map ---');

        $wp = DB::connection('wordpress');

        // Category redirects: /product-category/{slug} → /category/{slug}
        $categories = $wp->table('term_taxonomy as tt')
            ->join('terms as t', 't.term_id', '=', 'tt.term_id')
            ->where('tt.taxonomy', 'product_cat')
            ->select('t.slug')
            ->get();

        $count = 0;
        foreach ($categories as $cat) {
            // Get translations
            $translations = $this->getTermTranslations($cat->slug, 'tax_product_cat');

            // Default (ka)
            DB::table('redirect_map')->insertOrIgnore([
                'old_url' => "/product-category/{$cat->slug}/",
                'new_url' => "/category/{$cat->slug}",
                'status_code' => 301,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;

            // English and Russian versions
            foreach (['en', 'ru'] as $lang) {
                if (isset($translations[$lang])) {
                    $slug = $translations[$lang]['slug'];
                    DB::table('redirect_map')->insertOrIgnore([
                        'old_url' => "/{$lang}/product-category/{$slug}/",
                        'new_url' => "/{$lang}/category/{$slug}",
                        'status_code' => 301,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $count++;
                }
            }
        }

        $this->info("Generated {$count} redirects.");
        return self::SUCCESS;
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------
    private function getTermTranslations(int $termId, string $elementType): array
    {
        $wp = DB::connection('wordpress');

        $trid = $wp->table('icl_translations')
            ->where('element_id', $termId)
            ->where('element_type', $elementType)
            ->value('trid');

        if (! $trid) {
            return [];
        }

        $translations = $wp->table('icl_translations')
            ->where('trid', $trid)
            ->where('element_type', $elementType)
            ->get();

        $result = [];
        foreach ($translations as $trans) {
            $term = $wp->table('terms')
                ->where('term_id', $trans->element_id)
                ->first();

            if ($term) {
                $result[$trans->language_code] = [
                    'name' => $term->name,
                    'slug' => $term->slug,
                ];
            }
        }

        return $result;
    }

    private function buildTranslatedField(array $translations, string $field, string $default): array
    {
        $result = ['ka' => $default];

        foreach ($translations as $lang => $data) {
            $result[$lang] = $data[$field] ?? $default;
        }

        return $result;
    }
}
