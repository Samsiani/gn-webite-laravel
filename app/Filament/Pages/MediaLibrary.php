<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaLibrary extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Catalog';
    protected static ?int $navigationSort = 6;
    protected static ?string $title = 'Media Library';
    protected static ?string $slug = 'media-library';
    protected static string $view = 'filament.pages.media-library';

    public string $search = '';
    public string $filterCollection = '';
    public $upload;

    public function updatedUpload(): void
    {
        $this->validate([
            'upload' => 'image|max:10240',
        ]);
    }

    public function uploadMedia(): void
    {
        $this->validate([
            'upload' => 'required|image|max:10240',
        ]);

        // Store as unattached media for later use
        $path = $this->upload->store('media-library', 'public');

        $this->upload = null;
        $this->dispatch('$refresh');
    }

    public function deleteMedia(int $id): void
    {
        $media = Media::find($id);
        if ($media) {
            $media->delete();
        }
    }

    public function getMediaProperty()
    {
        $query = Media::query()->latest();

        if ($this->search) {
            $query->where('file_name', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('name', 'LIKE', '%' . $this->search . '%');
        }

        if ($this->filterCollection) {
            $query->where('collection_name', $this->filterCollection);
        }

        return $query->paginate(24);
    }

    public function getCollectionsProperty(): array
    {
        return Media::select('collection_name')
            ->distinct()
            ->pluck('collection_name')
            ->toArray();
    }
}
