<?php

namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Filament\Resources\BlogPostResource;
use App\Models\BlogPostTag;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogPost extends CreateRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->tags = $data['post_tags'] ?? [];
        unset($data['post_tags']);
        return $data;
    }

    private array $tags = [];

    protected function afterCreate(): void
    {
        foreach ($this->tags as $tag) {
            BlogPostTag::create([
                'blog_post_id' => $this->record->id,
                'tag' => $tag,
            ]);
        }
    }
}
