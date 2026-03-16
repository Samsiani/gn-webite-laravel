<?php

namespace App\Console\Commands;

use App\Models\ProductSpec;
use App\Models\SpecAttribute;
use App\Models\SpecAttributeValue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;
use Lunar\Models\Url;

class ImportFromWooCommerce extends Command
{
    protected $signature = 'gn:import-wc
                            {--limit=50 : Number of products to import per category}
                            {--page=1 : API page number}
                            {--category= : WC category ID to import from}
                            {--images : Download and attach product images}
                            {--per-category=5 : Products per category}';

    protected $description = 'Import products from gn.ge WooCommerce REST API with WPML translations';

    private string $baseUrl;
    private string $ck;
    private string $cs;
    private ?Currency $currency;
    private ?ProductType $productType;
    private ?CollectionGroup $collectionGroup;
    private array $languageIds;

    public function handle(): int
    {
        $this->baseUrl = rtrim(config('services.woocommerce.url', 'https://gn.ge'), '/');
        $this->ck = config('services.woocommerce.key', '');
        $this->cs = config('services.woocommerce.secret', '');

        if (! $this->ck || ! $this->cs) {
            $this->error('Set WC_KEY and WC_SECRET in .env');
            return self::FAILURE;
        }

        $this->currency = Currency::where('code', 'GEL')->first();
        $this->productType = ProductType::first();
        $this->collectionGroup = CollectionGroup::where('handle', 'product-categories')->first();
        $this->languageIds = Language::all()->pluck('id', 'code')->toArray();

        $this->info("Connecting to {$this->baseUrl}...");
        if (! $this->wcGet('products', ['per_page' => 1])) {
            return self::FAILURE;
        }
        $this->info('Connected.');

        $this->importCategories();

        if ($this->option('category')) {
            $this->importProducts((int) $this->option('category'), (int) $this->option('limit'));
        } else {
            $this->importFromAllCategories();
        }

        $this->newLine();
        $this->info('Import complete!');
        $this->line('Products: ' . Product::count() . ', Specs: ' . ProductSpec::count() . ', Images: ' . DB::table('media')->count());
        return self::SUCCESS;
    }

    private function importFromAllCategories(): void
    {
        $perCat = (int) $this->option('per-category');
        $categories = DB::table('wp_id_map')->where('entity_type', 'category')->get();
        foreach ($categories as $catMap) {
            $col = LunarCollection::find($catMap->lunar_id);
            $this->info("--- {$col?->translateAttribute('name')} (WC:{$catMap->wp_id}) ---");
            $this->importProducts($catMap->wp_id, $perCat);
        }
    }

    private function importCategories(): void
    {
        $this->info('--- Categories ---');
        $allCats = collect();
        $page = 1;
        while (true) {
            $cats = $this->wcGet('products/categories', ['per_page' => 100, 'page' => $page]);
            if (! $cats || $cats->isEmpty()) break;
            $allCats = $allCats->merge($cats);
            if ($cats->count() < 100) break;
            $page++;
        }

        foreach ($allCats as $cat) {
            if (DB::table('wp_id_map')->where('entity_type', 'category')->where('wp_id', $cat['id'])->exists()) continue;

            // Translations
            $transIds = $cat['translations'] ?? [];
            $names = ['ka' => $cat['name']];
            $slugs = ['ka' => urldecode($cat['slug'])];

            foreach (['en', 'ru'] as $lang) {
                $tid = $transIds[$lang] ?? null;
                if ($tid && $tid != $cat['id']) {
                    $t = $this->wcGet("products/categories/{$tid}");
                    if ($t && ! empty($t['name'])) {
                        $names[$lang] = $t['name'];
                        $slugs[$lang] = urldecode($t['slug'] ?? $cat['slug']);
                    }
                }
            }

            $collection = LunarCollection::create([
                'collection_group_id' => $this->collectionGroup->id,
                'attribute_data' => [
                    'name' => new TranslatedText(collect($names)->map(fn ($v) => new Text($v))),
                ],
            ]);

            foreach ($slugs as $lang => $slug) {
                if (isset($this->languageIds[$lang])) {
                    Url::create([
                        'element_type' => 'collection',
                        'element_id' => $collection->id,
                        'language_id' => $this->languageIds[$lang],
                        'slug' => Str::slug($slug) ?: Str::slug($names[$lang]),
                        'default' => $lang === 'ka',
                    ]);
                }
            }

            // Category image
            $catImgUrl = $cat['image']['src'] ?? null;
            if ($catImgUrl) {
                try {
                    $imgResponse = Http::withHeaders(['User-Agent' => 'GN-Import/1.0'])->timeout(30)->get($catImgUrl);
                    if ($imgResponse->ok()) {
                        $webpBytes = $this->convertToWebp($imgResponse->body());
                        $filename = Str::slug(pathinfo(urldecode($catImgUrl), PATHINFO_FILENAME)) ?: 'category-' . $cat['id'];
                        $collection->addMediaFromString($webpBytes ?? $imgResponse->body())
                            ->usingFileName($filename . ($webpBytes ? '.webp' : '.' . pathinfo($catImgUrl, PATHINFO_EXTENSION)))
                            ->toMediaCollection('images');
                        $this->line("    img: {$names['ka']}");
                    }
                } catch (\Exception $e) {
                    $this->warn("    img fail: {$e->getMessage()}");
                }
            }

            DB::table('wp_id_map')->insert([
                'entity_type' => 'category', 'wp_id' => $cat['id'],
                'lunar_id' => $collection->id, 'lang' => null,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        $this->info("  {$allCats->count()} categories processed.");
    }

    private function importProducts(int $categoryId, int $limit): void
    {
        $products = $this->wcGet('products', [
            'per_page' => min($limit, 100),
            'page' => (int) $this->option('page'),
            'status' => 'publish',
            'category' => $categoryId,
            'orderby' => 'date',
            'order' => 'desc',
        ]);

        if (! $products || $products->isEmpty()) {
            $this->line('  No products.');
            return;
        }

        $imported = 0;
        foreach ($products as $wc) {
            try {
                if ($this->importProduct($wc)) {
                    $this->line("  ✓ {$wc['name']}");
                    $imported++;
                }
            } catch (\Exception $e) {
                $this->warn("  ✗ #{$wc['id']}: {$e->getMessage()}");
            }
        }
        $this->line("  Imported: {$imported}");
    }

    private function importProduct(array $wc): bool
    {
        $wcId = $wc['id'];
        if (DB::table('wp_id_map')->where('entity_type', 'product')->where('wp_id', $wcId)->exists()) {
            return false;
        }

        // Skip products without price or with 0 price
        $checkPrice = (float) ($wc['regular_price'] ?: $wc['price'] ?: 0);
        if ($checkPrice <= 0) {
            return false;
        }

        // ── Fetch all language versions ──
        $transIds = $wc['translations'] ?? [];
        $versions = ['ka' => $wc];

        foreach (['en', 'ru'] as $lang) {
            $tid = $transIds[$lang] ?? null;
            if ($tid && $tid != $wcId) {
                $t = $this->wcGet("products/{$tid}");
                if ($t && ! empty($t['name'])) {
                    $versions[$lang] = $t instanceof \Illuminate\Support\Collection ? $t->toArray() : (array) $t;
                }
            }
        }

        // ── Build translated text fields ──
        // Logic: if WC has short_description → use as our description, skip WC description
        //        if no short_description → use WC description as our short_description
        $names = [];
        $descriptions = [];
        $shortDescriptions = [];
        $slugs = [];

        foreach ($versions as $lang => $ver) {
            $names[$lang] = $ver['name'] ?? $wc['name'];
            $wcShort = trim($ver['short_description'] ?? '');
            $wcFull = trim($ver['description'] ?? '');

            if ($wcShort !== '' && strip_tags($wcShort) !== '') {
                // Has short_description → use it as our description, skip full description
                $descriptions[$lang] = $wcShort;
                $shortDescriptions[$lang] = '';
            } else {
                // No short_description → use full description as our short_description
                $descriptions[$lang] = '';
                $shortDescriptions[$lang] = $wcFull;
            }
            $slugs[$lang] = urldecode($ver['slug'] ?? $wc['slug']);
        }

        $attributeData = [
            'name' => new TranslatedText(collect($names)->map(fn ($v) => new Text($v))),
            'description' => new TranslatedText(collect($descriptions)->map(fn ($v) => new Text($v))),
            'short_description' => new TranslatedText(collect($shortDescriptions)->map(fn ($v) => new Text($v))),
        ];

        // ── Create product ──
        $product = Product::create([
            'product_type_id' => $this->productType->id,
            'status' => 'published',
            'attribute_data' => $attributeData,
        ]);

        // ── Create variant ──
        $sku = $wc['sku'] ?: 'WC-' . $wcId;
        $stock = max((int) ($wc['stock_quantity'] ?? 0), ($wc['stock_status'] ?? '') === 'instock' ? 10 : 0);

        // Extract dimensions/weight from WC
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'tax_class_id' => 1,
            'sku' => $sku,
            'stock' => $stock,
            'length_value' => ! empty($wc['dimensions']['length']) ? (float) $wc['dimensions']['length'] : null,
            'width_value' => ! empty($wc['dimensions']['width']) ? (float) $wc['dimensions']['width'] : null,
            'height_value' => ! empty($wc['dimensions']['height']) ? (float) $wc['dimensions']['height'] : null,
            'weight_value' => ! empty($wc['weight']) ? (float) $wc['weight'] : null,
        ]);

        // ── Price ──
        $regular = (float) ($wc['regular_price'] ?: $wc['price'] ?: 0);
        $sale = (float) ($wc['sale_price'] ?: 0);
        if ($regular > 0) {
            Price::create([
                'priceable_type' => 'product_variant',
                'priceable_id' => $variant->id,
                'currency_id' => $this->currency->id,
                'min_quantity' => 1,
                'price' => ($sale > 0 && $sale < $regular) ? (int) round($sale * 100) : (int) round($regular * 100),
                'compare_price' => ($sale > 0 && $sale < $regular) ? (int) round($regular * 100) : null,
            ]);
        }

        // ── URLs ──
        foreach ($slugs as $lang => $slug) {
            if (! isset($this->languageIds[$lang])) continue;
            $finalSlug = Str::slug($slug) ?: Str::slug($names[$lang]);
            if (! $finalSlug) continue;
            $base = $finalSlug;
            $c = 0;
            while (Url::where('slug', $finalSlug)->where('element_type', 'product')->where('language_id', $this->languageIds[$lang])->exists()) {
                $finalSlug = $base . '-' . (++$c);
            }
            Url::create([
                'element_type' => 'product',
                'element_id' => $product->id,
                'language_id' => $this->languageIds[$lang],
                'slug' => $finalSlug,
                'default' => $lang === 'ka',
            ]);
        }

        // ── Collections ──
        foreach ($wc['categories'] ?? [] as $wcCat) {
            $map = DB::table('wp_id_map')->where('entity_type', 'category')->where('wp_id', $wcCat['id'])->first();
            if ($map) {
                DB::table('lunar_collection_product')->insertOrIgnore([
                    'collection_id' => $map->lunar_id, 'product_id' => $product->id, 'position' => 0,
                ]);
            }
        }

        // ── Specs / Attributes ──
        $kaAttrs = $versions['ka']['attributes'] ?? [];
        $enAttrs = collect($versions['en']['attributes'] ?? [])->keyBy('name');
        $ruAttrs = collect($versions['ru']['attributes'] ?? [])->keyBy('name');

        $position = 0;

        // First: add dimensions as "ზომები" spec if WC has dimensions
        $dims = $wc['dimensions'] ?? [];
        if (! empty($dims['length']) || ! empty($dims['width']) || ! empty($dims['height'])) {
            $parts = array_filter([
                ! empty($dims['length']) ? $dims['length'] : null,
                ! empty($dims['width']) ? $dims['width'] : null,
                ! empty($dims['height']) ? $dims['height'] : null,
            ]);
            if (! empty($parts)) {
                $dimValue = implode(' × ', $parts) . ' სმ';
                $dimValueEn = implode(' × ', $parts) . ' cm';
                $dimValueRu = implode(' × ', $parts) . ' см';
                $specAttr = SpecAttribute::firstOrCreate(
                    ['name' => 'ზომები'],
                    ['name_en' => 'Dimensions', 'name_ru' => 'Размеры', 'position' => 0]
                );
                ProductSpec::create([
                    'product_id' => $product->id,
                    'spec_attribute_id' => $specAttr->id,
                    'value' => $dimValue,
                    'value_en' => $dimValueEn,
                    'value_ru' => $dimValueRu,
                    'position' => $position++,
                ]);
                SpecAttributeValue::firstOrCreate([
                    'spec_attribute_id' => $specAttr->id, 'value' => $dimValue,
                ]);
            }
        }

        // Second: add weight as "წონა" spec if WC has weight
        if (! empty($wc['weight'])) {
            $specAttr = SpecAttribute::firstOrCreate(
                ['name' => 'წონა'],
                ['name_en' => 'Weight', 'name_ru' => 'Вес', 'position' => 1]
            );
            $weightVal = $wc['weight'] . ' კგ';
            $weightValEn = $wc['weight'] . ' kg';
            $weightValRu = $wc['weight'] . ' кг';
            ProductSpec::create([
                'product_id' => $product->id,
                'spec_attribute_id' => $specAttr->id,
                'value' => $weightVal,
                'value_en' => $weightValEn,
                'value_ru' => $weightValRu,
                'position' => $position++,
            ]);
            SpecAttributeValue::firstOrCreate([
                'spec_attribute_id' => $specAttr->id, 'value' => $weightVal,
            ]);
        }

        // Third: all WC product attributes
        $skipAttributes = ['მდგომარეობა', 'Condition', 'Состояние'];

        foreach ($kaAttrs as $attr) {
            $attrName = $attr['name'];
            if (in_array($attrName, $skipAttributes)) continue;
            $kaValue = implode(', ', $attr['options']);
            if (! $kaValue) continue;

            $enAttr = $enAttrs->get($attrName);
            $ruAttr = $ruAttrs->get($attrName);
            $enValue = $enAttr ? implode(', ', $enAttr['options']) : $kaValue;
            $ruValue = $ruAttr ? implode(', ', $ruAttr['options']) : $kaValue;

            $specAttr = SpecAttribute::where('name', $attrName)->first();
            if (! $specAttr) {
                $specAttr = SpecAttribute::create([
                    'name' => $attrName,
                    'name_en' => null,
                    'name_ru' => null,
                    'position' => SpecAttribute::max('position') + 1,
                ]);
            }

            ProductSpec::create([
                'product_id' => $product->id,
                'spec_attribute_id' => $specAttr->id,
                'value' => $kaValue,
                'value_en' => ($enValue !== $kaValue) ? $enValue : null,
                'value_ru' => ($ruValue !== $kaValue) ? $ruValue : null,
                'position' => $position++,
            ]);

            foreach ([$kaValue, $enValue, $ruValue] as $v) {
                if ($v) {
                    SpecAttributeValue::firstOrCreate([
                        'spec_attribute_id' => $specAttr->id, 'value' => $v,
                    ]);
                }
            }
        }

        // ── Images ──
        if ($this->option('images')) {
            foreach ($wc['images'] ?? [] as $i => $img) {
                $imgUrl = $img['src'] ?? '';
                if (! $imgUrl) continue;
                $collection = $i === 0 ? 'images' : 'gallery';

                try {
                    // Download image bytes
                    $imageBytes = null;
                    try {
                        $response = Http::withHeaders(['User-Agent' => 'GN-Import/1.0'])->timeout(30)->get($imgUrl);
                        if ($response->ok()) {
                            $imageBytes = $response->body();
                        }
                    } catch (\Exception $e) {
                        // skip
                    }

                    if (! $imageBytes) continue;

                    // Convert to WebP
                    $webpBytes = $this->convertToWebp($imageBytes);
                    $filename = Str::slug(pathinfo(urldecode($imgUrl), PATHINFO_FILENAME)) ?: 'image-' . $i;

                    $product->addMediaFromString($webpBytes ?? $imageBytes)
                        ->usingFileName($filename . ($webpBytes ? '.webp' : '.' . pathinfo($imgUrl, PATHINFO_EXTENSION)))
                        ->toMediaCollection($collection);
                } catch (\Exception $e) {
                    // skip
                }
            }
        }

        // ── ID Mapping ──
        DB::table('wp_id_map')->insert([
            'entity_type' => 'product', 'wp_id' => $wcId,
            'lunar_id' => $product->id, 'lang' => 'ka',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach ($transIds as $lang => $tid) {
            if ($tid != $wcId) {
                DB::table('wp_id_map')->insertOrIgnore([
                    'entity_type' => 'product', 'wp_id' => $tid,
                    'lunar_id' => $product->id, 'lang' => str_replace('-ge', '', $lang),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        return true;
    }

    private function convertToWebp(string $imageBytes): ?string
    {
        $image = @imagecreatefromstring($imageBytes);
        if (! $image) {
            return null;
        }

        // Preserve transparency
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        ob_start();
        imagewebp($image, null, 90);
        $webpBytes = ob_get_clean();
        imagedestroy($image);

        return $webpBytes ?: null;
    }

    private function wcGet(string $endpoint, array $params = []): mixed
    {
        try {
            $r = Http::withBasicAuth($this->ck, $this->cs)
                ->withHeaders(['User-Agent' => 'GN-Import/1.0'])
                ->timeout(30)
                ->get("{$this->baseUrl}/wp-json/wc/v3/{$endpoint}", $params);
            return $r->ok() ? collect($r->json()) : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
