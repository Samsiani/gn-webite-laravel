<?php

namespace App\Services;

use App\Models\ProductSpec;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\Product;

class ShortcodeParser
{
    /**
     * Parse shortcodes in HTML content and replace with rendered blocks.
     */
    public static function parse(string $content, ?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        // [products] — product grid
        $content = preg_replace_callback(
            '/\[products([^\]]*)\]/',
            fn ($m) => self::renderProducts(self::parseAttrs($m[1]), $locale),
            $content
        );

        // [product] — single product card
        $content = preg_replace_callback(
            '/\[product([^\]]*)\]/',
            fn ($m) => self::renderProduct(self::parseAttrs($m[1]), $locale),
            $content
        );

        // [cta] — call to action button
        $content = preg_replace_callback(
            '/\[cta([^\]]*)\](.*?)\[\/cta\]/s',
            fn ($m) => self::renderCta(self::parseAttrs($m[1]), trim($m[2])),
            $content
        );

        // [note] — info/warning box
        $content = preg_replace_callback(
            '/\[note([^\]]*)\](.*?)\[\/note\]/s',
            fn ($m) => self::renderNote(self::parseAttrs($m[1]), trim($m[2])),
            $content
        );

        return $content;
    }

    /**
     * [products category="მაცივრები" limit="4" columns="4"]
     * [products ids="52,53,54"]
     * [products latest="6"]
     * [products sale="4"]
     */
    private static function renderProducts(array $attrs, string $locale): string
    {
        $limit = (int) ($attrs['limit'] ?? $attrs['latest'] ?? $attrs['sale'] ?? 4);
        $columns = (int) ($attrs['columns'] ?? 4);
        $prefix = $locale === 'ka' ? '' : '/' . $locale;

        $query = Product::where('status', 'published')
            ->with(['variants.prices', 'urls.language', 'media']);

        // Filter by category name
        if (! empty($attrs['category'])) {
            $col = LunarCollection::all()->first(function ($c) use ($attrs) {
                return $c->translateAttribute('name') === $attrs['category']
                    || $c->translateAttribute('name', 'en') === $attrs['category'];
            });
            if ($col) {
                $query->whereHas('collections', fn ($q) => $q->where('lunar_collections.id', $col->id));
            }
        }

        // Filter by IDs
        if (! empty($attrs['ids'])) {
            $ids = array_map('intval', explode(',', $attrs['ids']));
            $query->whereIn('id', $ids);
        }

        // Filter by SKU
        if (! empty($attrs['sku'])) {
            $skus = array_map('trim', explode(',', $attrs['sku']));
            $query->whereHas('variants', fn ($q) => $q->whereIn('sku', $skus));
        }

        // Sale products only
        if (! empty($attrs['sale'])) {
            $query->whereHas('variants.prices', fn ($q) => $q->whereNotNull('compare_price')->where('compare_price', '>', 0));
        }

        $products = $query->limit($limit)->latest()->get();

        if ($products->isEmpty()) return '';

        $gridCols = match ($columns) {
            2 => 'grid-cols-1 sm:grid-cols-2',
            3 => 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3',
            default => 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4',
        };

        $html = '<div class="my-6 not-prose"><div class="grid ' . $gridCols . ' gap-3">';

        foreach ($products as $product) {
            $name = $product->translateAttribute('name', $locale) ?? $product->translateAttribute('name');
            $variant = $product->variants->first();
            $priceObj = $variant?->prices->first();
            $price = $priceObj ? number_format($priceObj->price->value / 100, 2) : null;
            $comparePrice = ($priceObj?->compare_price && $priceObj->compare_price->value > 0) ? number_format($priceObj->compare_price->value / 100, 2) : null;
            $onSale = $comparePrice && (float) str_replace(',', '', $comparePrice) > (float) str_replace(',', '', $price);
            $image = $product->getFirstMediaUrl('images') ?: $product->getFirstMediaUrl('gallery');
            $url = $product->urls->first(fn ($u) => $u->language?->code === $locale) ?? $product->urls->firstWhere('default', true);
            $slug = $url?->slug ?? '';
            $sku = $variant?->sku;

            $html .= '<a href="' . $prefix . '/product/' . $slug . '" class="product-card bg-white rounded-xl border border-gray-100 overflow-hidden group block">';
            $html .= '<div class="aspect-square bg-white overflow-hidden relative">';
            if ($image) {
                $html .= '<img src="' . $image . '" alt="' . e($name) . '" loading="lazy" class="w-full h-full object-contain p-3">';
            }
            if ($onSale) {
                $pct = round((1 - (float) str_replace(',', '', $price) / (float) str_replace(',', '', $comparePrice)) * 100);
                $html .= '<span class="absolute top-2 right-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-md">-' . $pct . '%</span>';
            }
            $html .= '</div>';
            $html .= '<div class="p-3 border-t border-gray-50">';
            $html .= '<h3 style="font-size:14px;min-height:1.5em" class="font-medium text-gray-800 line-clamp-2 group-hover:text-primary transition leading-snug">' . e($name) . '</h3>';
            if ($sku) $html .= '<p class="mt-0.5" style="font-size:11px;line-height:1;color:#444">SKU:' . e($sku) . '</p>';
            if ($price) {
                $html .= '<div class="mt-1.5 flex items-baseline gap-1.5">';
                if ($onSale) $html .= '<span class="text-xs text-gray-400 line-through">' . $comparePrice . '</span>';
                $html .= '<span class="text-base font-bold text-primary">' . $price . ' <span class="text-xs">₾</span></span>';
                $html .= '</div>';
            }
            $html .= '</div></a>';
        }

        $html .= '</div></div>';
        return $html;
    }

    /**
     * [product sku="CY828"] — single product highlight
     */
    private static function renderProduct(array $attrs, string $locale): string
    {
        $prefix = $locale === 'ka' ? '' : '/' . $locale;
        $product = null;

        if (! empty($attrs['sku'])) {
            $product = Product::whereHas('variants', fn ($q) => $q->where('sku', $attrs['sku']))
                ->with(['variants.prices', 'urls.language', 'media'])->first();
        } elseif (! empty($attrs['id'])) {
            $product = Product::with(['variants.prices', 'urls.language', 'media'])->find($attrs['id']);
        }

        if (! $product) return '';

        $name = $product->translateAttribute('name', $locale) ?? $product->translateAttribute('name');
        $variant = $product->variants->first();
        $priceObj = $variant?->prices->first();
        $price = $priceObj ? number_format($priceObj->price->value / 100, 2) : null;
        $image = $product->getFirstMediaUrl('images') ?: $product->getFirstMediaUrl('gallery');
        $url = $product->urls->first(fn ($u) => $u->language?->code === $locale) ?? $product->urls->firstWhere('default', true);
        $slug = $url?->slug ?? '';

        $specs = ProductSpec::where('product_id', $product->id)->orderBy('position')->with('attribute')->limit(5)->get();

        $html = '<div class="my-6 not-prose bg-white rounded-xl border border-gray-100 p-4 flex gap-4 items-center">';
        if ($image) {
            $html .= '<a href="' . $prefix . '/product/' . $slug . '" class="w-24 h-24 bg-gray-50 rounded-lg overflow-hidden shrink-0">';
            $html .= '<img src="' . $image . '" alt="' . e($name) . '" class="w-full h-full object-contain p-2"></a>';
        }
        $html .= '<div class="flex-1 min-w-0">';
        $html .= '<a href="' . $prefix . '/product/' . $slug . '" class="font-semibold text-gray-900 hover:text-primary transition">' . e($name) . '</a>';
        if ($variant?->sku) $html .= '<p style="font-size:11px;color:#8d8f92" class="mt-0.5">SKU:' . e($variant->sku) . '</p>';
        if (! $specs->isEmpty()) {
            $html .= '<div class="mt-1.5 flex flex-wrap gap-x-4 gap-y-0.5 text-xs text-gray-500">';
            foreach ($specs as $s) {
                $html .= '<span>' . e($s->attribute->name) . ': <strong class="text-gray-700">' . e($s->value) . '</strong></span>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        if ($price) {
            $html .= '<div class="text-right shrink-0"><span class="text-lg font-bold text-primary">' . $price . ' ₾</span></div>';
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * [cta url="/shop" color="primary"]Shop Now[/cta]
     */
    private static function renderCta(array $attrs, string $text): string
    {
        $url = $attrs['url'] ?? '/shop';
        $color = $attrs['color'] ?? 'primary';
        $bgClass = $color === 'green' ? 'bg-green-600 hover:bg-green-700' : 'bg-primary hover:bg-primary-dark';

        return '<div class="my-6 not-prose"><a href="' . e($url) . '" class="inline-flex items-center gap-2 ' . $bgClass . ' text-white font-semibold px-6 py-3 rounded-xl transition text-sm">' . e($text) . '</a></div>';
    }

    /**
     * [note type="info"]Text here[/note]
     * [note type="warning"]Text here[/note]
     */
    private static function renderNote(array $attrs, string $text): string
    {
        $type = $attrs['type'] ?? 'info';
        $styles = match ($type) {
            'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
            'success' => 'bg-green-50 border-green-200 text-green-800',
            'danger' => 'bg-red-50 border-red-200 text-red-800',
            default => 'bg-blue-50 border-blue-200 text-blue-800',
        };

        return '<div class="my-4 not-prose rounded-xl border p-4 text-sm ' . $styles . '">' . $text . '</div>';
    }

    private static function parseAttrs(string $str): array
    {
        $attrs = [];
        preg_match_all('/(\w+)=["\']([^"\']*)["\']/', $str, $matches, PREG_SET_ORDER);
        foreach ($matches as $m) {
            $attrs[$m[1]] = $m[2];
        }
        return $attrs;
    }
}
