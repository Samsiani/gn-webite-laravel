<?php

namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Filament\Resources\BlogPostResource;
use App\Models\BlogPostTag;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBlogPost extends EditRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['post_tags'] = $this->record->tags->pluck('tag')->toArray();
        return $data;
    }

    private array $tags = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->tags = $data['post_tags'] ?? [];
        unset($data['post_tags']);
        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->tags()->delete();
        foreach ($this->tags as $tag) {
            BlogPostTag::create([
                'blog_post_id' => $this->record->id,
                'tag' => $tag,
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view')
                ->label('View Post')
                ->icon('heroicon-o-eye')
                ->url(fn () => url('/blog/' . $this->record->slug))
                ->openUrlInNewTab()
                ->color('gray'),
            Actions\DeleteAction::make(),
        ];
    }
}
