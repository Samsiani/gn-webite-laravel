<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class TranslationManager extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-language';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 10;
    protected static ?string $title = 'Translations';
    protected static ?string $slug = 'translations';
    protected static string $view = 'filament.pages.translation-manager';

    public array $translations = [];
    public string $search = '';

    public function mount(): void
    {
        $this->loadTranslations();
    }

    public function loadTranslations(): void
    {
        $enFile = lang_path('en.json');
        $kaFile = lang_path('ka.json');
        $ruFile = lang_path('ru.json');

        $en = file_exists($enFile) ? json_decode(file_get_contents($enFile), true) : [];
        $ka = file_exists($kaFile) ? json_decode(file_get_contents($kaFile), true) : [];
        $ru = file_exists($ruFile) ? json_decode(file_get_contents($ruFile), true) : [];

        $this->translations = [];
        foreach ($en as $key => $value) {
            $this->translations[] = [
                'key' => $key,
                'en' => $value,
                'ka' => $ka[$key] ?? '',
                'ru' => $ru[$key] ?? '',
            ];
        }
    }

    public function save(): void
    {
        $en = [];
        $ka = [];
        $ru = [];

        foreach ($this->translations as $t) {
            $key = $t['key'];
            $en[$key] = $t['en'];
            $ka[$key] = $t['ka'];
            $ru[$key] = $t['ru'];
        }

        file_put_contents(lang_path('en.json'), json_encode($en, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        file_put_contents(lang_path('ka.json'), json_encode($ka, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        file_put_contents(lang_path('ru.json'), json_encode($ru, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        Notification::make()
            ->title('Translations saved successfully')
            ->success()
            ->send();
    }

    public function addTranslation(): void
    {
        $this->translations[] = [
            'key' => 'new_key_' . count($this->translations),
            'en' => '',
            'ka' => '',
            'ru' => '',
        ];
    }

    public function removeTranslation(int $index): void
    {
        unset($this->translations[$index]);
        $this->translations = array_values($this->translations);
    }

    public function getFilteredTranslations(): array
    {
        if (! $this->search) {
            return $this->translations;
        }

        $search = mb_strtolower($this->search);
        return array_filter($this->translations, function ($t) use ($search) {
            return str_contains(mb_strtolower($t['key']), $search)
                || str_contains(mb_strtolower($t['en']), $search)
                || str_contains(mb_strtolower($t['ka']), $search)
                || str_contains(mb_strtolower($t['ru']), $search);
        });
    }
}
