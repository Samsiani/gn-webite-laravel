<?php

namespace App\Livewire\Storefront;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Services\StorefrontData;
use Livewire\Component;
use Livewire\WithPagination;

class BlogListPage extends Component
{
    use WithPagination;

    public ?string $categorySlug = null;

    public function mount(?string $category = null): void
    {
        $this->categorySlug = $category;
    }

    public function render()
    {
        $locale = app()->getLocale();

        $query = BlogPost::published()->with(['category', 'media'])->latest('published_at');

        // Filter by category
        $activeCategory = null;
        if ($this->categorySlug) {
            $field = match ($locale) {
                'en' => 'slug_en',
                'ru' => 'slug_ru',
                default => 'slug',
            };
            $activeCategory = BlogCategory::where($field, $this->categorySlug)
                ->orWhere('slug', $this->categorySlug)
                ->first();

            if ($activeCategory) {
                $query->where('blog_category_id', $activeCategory->id);
            }
        }

        $posts = $query->paginate(9);
        $blogCategories = BlogCategory::withCount(['posts' => fn ($q) => $q->published()])->orderBy('position')->get();

        $categories = StorefrontData::categories();

        return view('livewire.storefront.blog-list-page', [
            'posts' => $posts,
            'blogCategories' => $blogCategories,
            'activeCategory' => $activeCategory,
        $blogTitle = $activeCategory ? $activeCategory->getTranslatedName(app()->getLocale()) : __('Blog');

        ])->layout('components.layouts.storefront', [
            'categories' => $categories,
            'metaTitle' => \App\Services\SeoHelper::title($blogTitle),
            'metaDescription' => __('Articles and guides about professional kitchen equipment, restaurant technology, and food industry trends.'),
            'canonical' => url(app()->getLocale() === 'ka' ? '/blog' : '/' . app()->getLocale() . '/blog'),
            'hreflangs' => ['ka' => url('/blog'), 'en' => url('/en/blog'), 'ru' => url('/ru/blog')],
        ]);
    }
}
