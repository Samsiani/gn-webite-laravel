<?php

namespace App\Livewire\Storefront;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Livewire\Component;
use Livewire\WithPagination;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\CollectionGroup;

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

        $collectionGroup = CollectionGroup::where('handle', 'product-categories')->first();
        $categories = $collectionGroup
            ? LunarCollection::where('collection_group_id', $collectionGroup->id)
                ->whereIsRoot()->with(['urls.language'])->get()
            : collect();

        return view('livewire.storefront.blog-list-page', [
            'posts' => $posts,
            'blogCategories' => $blogCategories,
            'activeCategory' => $activeCategory,
        ])->layout('components.layouts.storefront', ['categories' => $categories]);
    }
}
