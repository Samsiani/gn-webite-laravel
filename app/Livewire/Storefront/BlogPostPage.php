<?php

namespace App\Livewire\Storefront;

use App\Models\BlogPost;
use App\Services\StorefrontData;
use Livewire\Component;

class BlogPostPage extends Component
{
    public BlogPost $post;

    public function mount(string $slug): void
    {
        $locale = app()->getLocale();
        $field = match ($locale) {
            'en' => 'slug_en',
            'ru' => 'slug_ru',
            default => 'slug',
        };

        $this->post = BlogPost::published()
            ->where($field, $slug)
            ->orWhere('slug', $slug)
            ->with(['category', 'tags', 'media'])
            ->firstOrFail();
    }

    public function render()
    {
        $locale = app()->getLocale();

        // Recent posts for sidebar
        $recentPosts = BlogPost::published()
            ->where('id', '!=', $this->post->id)
            ->with('media')
            ->latest('published_at')
            ->limit(5)
            ->get();

        // Hreflang
        $hreflangs = [];
        if ($this->post->slug) {
            $hreflangs['ka'] = url('/blog/' . $this->post->slug);
        }
        if ($this->post->slug_en) {
            $hreflangs['en'] = url('/en/blog/' . $this->post->slug_en);
        }
        if ($this->post->slug_ru) {
            $hreflangs['ru'] = url('/ru/blog/' . $this->post->slug_ru);
        }

        $categories = StorefrontData::categories();

        return view('livewire.storefront.blog-post-page', [
            'recentPosts' => $recentPosts,
            'hreflangs' => $hreflangs,
            'locale' => $locale,
        ])->layout('components.layouts.storefront', [
            'categories' => $categories,
            'metaTitle' => $this->post->t('meta_title') ?: $this->post->t('title'),
            'metaDescription' => $this->post->t('meta_description') ?: \Illuminate\Support\Str::limit(strip_tags($this->post->t('excerpt')), 160),
            'hreflangs' => $hreflangs,
        ]);
    }
}
