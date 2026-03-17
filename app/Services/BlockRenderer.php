<?php

namespace App\Services;

use App\Models\ProductSpec;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\Product;

class BlockRenderer
{
    public static function render(array $blocks, ?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $html = '';

        foreach ($blocks as $block) {
            $type = $block['type'] ?? '';
            $data = $block['data'] ?? [];

            $html .= match ($type) {
                'text' => self::renderText($data),
                'products' => self::renderProducts($data, $locale),
                'product' => self::renderProduct($data, $locale),
                'image' => self::renderImage($data),
                'cta' => self::renderCta($data),
                'note' => self::renderNote($data),
                'html' => self::renderHtml($data),
                'spacer' => self::renderSpacer($data),
                default => '',
            };
        }

        return $html;
    }

    private static function renderText(array $data): string
    {
        $content = $data['content'] ?? '';
        if (! $content) return '';

        // Wrap tables in a scrollable container for mobile
        $content = preg_replace(
            '/<table/i',
            '<div class="overflow-x-auto -mx-1 px-1"><table',
            $content
        );
        $content = preg_replace(
            '/<\/table>/i',
            '</table></div>',
            $content
        );

        return '<div class="prose max-w-none text-gray-600 prose-headings:text-gray-900 prose-headings:font-bold prose-h2:text-xl prose-h3:text-lg prose-p:my-3 prose-p:leading-relaxed prose-strong:text-gray-800 prose-ul:list-disc prose-ul:pl-6 prose-ul:my-3 prose-ol:list-decimal prose-ol:pl-6 prose-ol:my-3 prose-li:my-1 prose-a:text-primary prose-a:underline prose-blockquote:border-l-primary prose-blockquote:bg-gray-50 prose-blockquote:py-1 prose-blockquote:px-4 prose-img:rounded-xl prose-table:w-full prose-table:border prose-table:border-gray-200 prose-table:rounded-lg prose-table:text-sm prose-th:bg-gray-50 prose-th:px-3 prose-th:py-2.5 prose-th:text-left prose-th:font-semibold prose-th:text-gray-700 prose-th:border-b prose-th:border-gray-200 prose-td:px-3 prose-td:py-2 prose-td:border-b prose-td:border-gray-100 prose-td:text-gray-600">' . $content . '</div>';
    }

    private static function renderProducts(array $data, string $locale): string
    {
        $source = $data['source'] ?? 'latest';
        $limit = (int) ($data['limit'] ?? 4);
        $columns = (int) ($data['columns'] ?? 4);
        $prefix = $locale === 'ka' ? '' : '/' . $locale;

        $query = Product::where('status', 'published')
            ->with(['variants.prices', 'urls.language', 'media']);

        match ($source) {
            'category' => (function () use ($query, $data) {
                $catName = $data['category'] ?? '';
                if ($catName) {
                    $col = LunarCollection::all()->first(fn ($c) => $c->translateAttribute('name') === $catName || $c->translateAttribute('name', 'en') === $catName);
                    if ($col) $query->whereHas('collections', fn ($q) => $q->where('lunar_collections.id', $col->id));
                }
            })(),
            'sale' => $query->whereHas('variants.prices', fn ($q) => $q->whereNotNull('compare_price')->where('compare_price', '>', 0)),
            'manual' => (function () use ($query, $data) {
                $skus = array_map('trim', explode(',', $data['skus'] ?? ''));
                if (! empty($skus)) $query->whereHas('variants', fn ($q) => $q->whereIn('sku', $skus));
            })(),
            default => null,
        };

        $products = $query->limit($limit)->latest()->get();
        if ($products->isEmpty()) return '';

        $gridCols = match ($columns) {
            2 => 'grid-cols-1 sm:grid-cols-2',
            3 => 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3',
            default => 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4',
        };

        $html = '<div class="my-6"><div class="grid ' . $gridCols . ' gap-3">';
        foreach ($products as $product) {
            $html .= self::buildProductCard($product, $locale, $prefix);
        }
        $html .= '</div></div>';
        return $html;
    }

    private static function renderProduct(array $data, string $locale): string
    {
        $prefix = $locale === 'ka' ? '' : '/' . $locale;
        $sku = $data['sku'] ?? '';
        if (! $sku) return '';

        $product = Product::whereHas('variants', fn ($q) => $q->where('sku', $sku))
            ->with(['variants.prices', 'urls.language', 'media'])->first();
        if (! $product) return '';

        $name = $product->translateAttribute('name', $locale) ?? $product->translateAttribute('name');
        $variant = $product->variants->first();
        $priceObj = $variant?->prices->first();
        $price = $priceObj ? number_format($priceObj->price->value / 100, 2) : null;
        $image = $product->getFirstMediaUrl('images', 'medium') ?: $product->getFirstMediaUrl('images') ?: $product->getFirstMediaUrl('gallery');
        $url = $product->urls->first(fn ($u) => $u->language?->code === $locale) ?? $product->urls->firstWhere('default', true);
        $slug = $url?->slug ?? '';

        $specs = ProductSpec::where('product_id', $product->id)->orderBy('position')->with('attribute')->limit(5)->get();

        $html = '<div class="my-6 bg-white rounded-xl border border-gray-100 p-4 flex gap-4 items-center">';
        if ($image) {
            $html .= '<a href="' . $prefix . '/product/' . $slug . '" class="w-24 h-24 bg-gray-50 rounded-lg overflow-hidden shrink-0">';
            $html .= '<img src="' . $image . '" alt="' . e($name) . '" class="w-full h-full object-contain p-2" loading="lazy" onload="this.classList.add(\'loaded\')"></a>';
        }
        $html .= '<div class="flex-1 min-w-0">';
        $html .= '<a href="' . $prefix . '/product/' . $slug . '" class="font-semibold text-gray-900 hover:text-primary transition">' . e($name) . '</a>';
        if ($variant?->sku) $html .= '<p style="font-size:11px;color:#8d8f92" class="mt-0.5">SKU:' . e($variant->sku) . '</p>';
        if ($specs->isNotEmpty()) {
            $html .= '<div class="mt-1.5 flex flex-wrap gap-x-4 gap-y-0.5 text-xs text-gray-500">';
            foreach ($specs as $s) {
                $html .= '<span>' . e($s->attribute->name) . ': <strong class="text-gray-700">' . e($s->value) . '</strong></span>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        if ($price) $html .= '<div class="text-right shrink-0"><span class="text-lg font-bold text-primary">' . $price . ' ₾</span></div>';
        $html .= '</div>';
        return $html;
    }

    private static function renderImage(array $data): string
    {
        $url = $data['url'] ?? '';
        if (! $url) return '';
        if (! str_starts_with($url, 'http')) $url = '/storage/' . $url;

        $alt = e($data['alt'] ?? '');
        $caption = $data['caption'] ?? '';
        $sizeClass = match ($data['size'] ?? 'full') {
            'medium' => 'max-w-2xl mx-auto',
            'small' => 'max-w-md mx-auto',
            default => '',
        };

        $html = '<figure class="my-6 ' . $sizeClass . '">';
        $html .= '<img src="' . $url . '" alt="' . $alt . '" class="w-full rounded-xl" loading="lazy">';
        if ($caption) $html .= '<figcaption class="text-center text-sm text-gray-500 mt-2">' . e($caption) . '</figcaption>';
        $html .= '</figure>';
        return $html;
    }

    private static function renderCta(array $data): string
    {
        $text = e($data['text'] ?? 'Click Here');
        $url = e($data['url'] ?? '/shop');
        $align = $data['align'] ?? 'left';
        $style = $data['style'] ?? 'primary';

        $btnClass = match ($style) {
            'outline' => 'border border-primary text-primary hover:bg-primary hover:text-white',
            'green' => 'bg-green-600 text-white hover:bg-green-700',
            default => 'bg-primary text-white hover:bg-primary-dark',
        };
        $alignClass = match ($align) {
            'center' => 'text-center',
            'right' => 'text-right',
            default => '',
        };

        return '<div class="my-6 ' . $alignClass . '"><a href="' . $url . '" class="inline-flex items-center gap-2 ' . $btnClass . ' font-semibold px-6 py-3 rounded-xl transition text-sm">' . $text . '</a></div>';
    }

    private static function renderNote(array $data): string
    {
        $type = $data['type'] ?? 'info';
        $content = $data['content'] ?? '';
        if (! $content) return '';

        $styles = match ($type) {
            'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
            'success' => 'bg-green-50 border-green-200 text-green-800',
            'danger' => 'bg-red-50 border-red-200 text-red-800',
            default => 'bg-blue-50 border-blue-200 text-blue-800',
        };
        $icon = match ($type) {
            'warning' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.834-1.964-.834-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>',
            'success' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
            'danger' => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
            default => '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        };

        return '<div class="my-4 rounded-xl border p-4 text-sm flex gap-3 ' . $styles . '">' . $icon . '<div>' . e($content) . '</div></div>';
    }

    private static function renderHtml(array $data): string
    {
        return '<div class="my-4">' . ($data['content'] ?? '') . '</div>';
    }

    private static function renderSpacer(array $data): string
    {
        $h = match ($data['size'] ?? 'md') {
            'sm' => 'h-4', 'lg' => 'h-16', 'xl' => 'h-24', default => 'h-8',
        };
        return '<div class="' . $h . '"></div>';
    }

    private static function buildProductCard($product, string $locale, string $prefix): string
    {
        $name = $product->translateAttribute('name', $locale) ?? $product->translateAttribute('name');
        $variant = $product->variants->first();
        $priceObj = $variant?->prices->first();
        $price = $priceObj ? number_format($priceObj->price->value / 100, 2) : null;
        $comparePrice = ($priceObj?->compare_price && $priceObj->compare_price->value > 0) ? number_format($priceObj->compare_price->value / 100, 2) : null;
        $onSale = $comparePrice && (float) str_replace(',', '', $comparePrice) > (float) str_replace(',', '', $price);
        $image = $product->getFirstMediaUrl('images', 'medium') ?: $product->getFirstMediaUrl('images') ?: $product->getFirstMediaUrl('gallery');
        $url = $product->urls->first(fn ($u) => $u->language?->code === $locale) ?? $product->urls->firstWhere('default', true);
        $slug = $url?->slug ?? '';
        $sku = $variant?->sku;

        $html = '<a href="' . $prefix . '/product/' . $slug . '" class="product-card bg-white rounded-xl border border-gray-100 overflow-hidden group block">';
        $html .= '<div class="aspect-square bg-white overflow-hidden relative">';
        if ($image) $html .= '<img src="' . $image . '" alt="' . e($name) . '" loading="lazy" onload="this.classList.add(\'loaded\')" class="w-full h-full object-contain p-3">';
        if ($onSale) {
            $pct = round((1 - (float) str_replace(',', '', $price) / (float) str_replace(',', '', $comparePrice)) * 100);
            $html .= '<span class="absolute top-2 right-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-md">-' . $pct . '%</span>';
        }
        $html .= '</div><div class="p-3 border-t border-gray-50">';
        $html .= '<h3 style="font-size:14px" class="font-medium text-gray-800 line-clamp-2 group-hover:text-primary transition leading-snug">' . e($name) . '</h3>';
        if ($sku) $html .= '<p class="mt-0.5" style="font-size:11px;line-height:1;color:#444">SKU:' . e($sku) . '</p>';
        if ($price) {
            $html .= '<div class="mt-1.5 flex items-baseline gap-1.5">';
            if ($onSale) $html .= '<span class="text-xs text-gray-400 line-through">' . $comparePrice . '</span>';
            $html .= '<span class="text-base font-bold text-primary">' . $price . ' <span class="text-xs">₾</span></span></div>';
        }
        $html .= '</div></a>';
        return $html;
    }
}
