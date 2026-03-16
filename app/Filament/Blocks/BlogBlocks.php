<?php

namespace App\Filament\Blocks;

use Filament\Forms;
use Filament\Forms\Components\Builder\Block;
use Lunar\Models\Collection as LunarCollection;

class BlogBlocks
{
    public static function all(): array
    {
        return [
            self::textBlock(),
            self::productsBlock(),
            self::productBlock(),
            self::imageBlock(),
            self::ctaBlock(),
            self::noteBlock(),
            self::htmlBlock(),
            self::spacerBlock(),
        ];
    }

    public static function textBlock(): Block
    {
        return Block::make('text')
            ->label('Text')
            ->icon('heroicon-o-document-text')
            ->schema([
                Forms\Components\RichEditor::make('content')
                    ->label('')
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('blog')
                    ->columnSpanFull(),
            ]);
    }

    public static function productsBlock(): Block
    {
        return Block::make('products')
            ->label('Product Collection')
            ->icon('heroicon-o-squares-2x2')
            ->schema([
                Forms\Components\Select::make('source')
                    ->label('Source')
                    ->options([
                        'category' => 'By Category',
                        'latest' => 'Latest Products',
                        'sale' => 'On Sale',
                        'manual' => 'Manual Selection (SKUs)',
                    ])
                    ->default('latest')
                    ->live()
                    ->required(),

                Forms\Components\Select::make('category')
                    ->label('Category')
                    ->options(fn () => LunarCollection::all()->mapWithKeys(fn ($c) => [
                        $c->translateAttribute('name') => $c->translateAttribute('name'),
                    ]))
                    ->searchable()
                    ->visible(fn (Forms\Get $get) => $get('source') === 'category'),

                Forms\Components\TextInput::make('skus')
                    ->label('SKUs (comma separated)')
                    ->placeholder('CY828, SK-68F, DZ500S')
                    ->visible(fn (Forms\Get $get) => $get('source') === 'manual'),

                Forms\Components\Select::make('limit')
                    ->label('Products to show')
                    ->options([2 => '2', 3 => '3', 4 => '4', 6 => '6', 8 => '8'])
                    ->default(4),

                Forms\Components\Select::make('columns')
                    ->label('Columns')
                    ->options([2 => '2', 3 => '3', 4 => '4'])
                    ->default(4),
            ])
            ->columns(2);
    }

    public static function productBlock(): Block
    {
        return Block::make('product')
            ->label('Single Product')
            ->icon('heroicon-o-tag')
            ->schema([
                Forms\Components\TextInput::make('sku')
                    ->label('Product SKU')
                    ->placeholder('CY828')
                    ->required(),
            ]);
    }

    public static function imageBlock(): Block
    {
        return Block::make('image')
            ->label('Image')
            ->icon('heroicon-o-photo')
            ->schema([
                Forms\Components\FileUpload::make('url')
                    ->label('Image')
                    ->image()
                    ->disk('public')
                    ->directory('blog')
                    ->required(),
                Forms\Components\TextInput::make('alt')
                    ->label('Alt Text'),
                Forms\Components\TextInput::make('caption')
                    ->label('Caption'),
                Forms\Components\Select::make('size')
                    ->label('Size')
                    ->options(['full' => 'Full Width', 'medium' => 'Medium (centered)', 'small' => 'Small (centered)'])
                    ->default('full'),
            ])
            ->columns(2);
    }

    public static function ctaBlock(): Block
    {
        return Block::make('cta')
            ->label('Call to Action')
            ->icon('heroicon-o-cursor-arrow-rays')
            ->schema([
                Forms\Components\TextInput::make('text')
                    ->label('Button Text')
                    ->required()
                    ->placeholder('Shop Now'),
                Forms\Components\TextInput::make('url')
                    ->label('URL')
                    ->required()
                    ->placeholder('/shop'),
                Forms\Components\Select::make('style')
                    ->label('Style')
                    ->options(['primary' => 'Primary', 'outline' => 'Outline', 'green' => 'Green'])
                    ->default('primary'),
                Forms\Components\Select::make('align')
                    ->label('Alignment')
                    ->options(['left' => 'Left', 'center' => 'Center', 'right' => 'Right'])
                    ->default('left'),
            ])
            ->columns(2);
    }

    public static function noteBlock(): Block
    {
        return Block::make('note')
            ->label('Note / Alert')
            ->icon('heroicon-o-exclamation-triangle')
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options(['info' => 'Info (blue)', 'warning' => 'Warning (yellow)', 'success' => 'Success (green)', 'danger' => 'Danger (red)'])
                    ->default('info')
                    ->required(),
                Forms\Components\Textarea::make('content')
                    ->label('Content')
                    ->required()
                    ->rows(3),
            ]);
    }

    public static function htmlBlock(): Block
    {
        return Block::make('html')
            ->label('Custom HTML')
            ->icon('heroicon-o-code-bracket')
            ->schema([
                Forms\Components\Textarea::make('content')
                    ->label('HTML Code')
                    ->rows(6)
                    ->columnSpanFull(),
            ]);
    }

    public static function spacerBlock(): Block
    {
        return Block::make('spacer')
            ->label('Spacer')
            ->icon('heroicon-o-arrows-up-down')
            ->schema([
                Forms\Components\Select::make('size')
                    ->label('Height')
                    ->options(['sm' => 'Small (16px)', 'md' => 'Medium (32px)', 'lg' => 'Large (64px)', 'xl' => 'Extra Large (96px)'])
                    ->default('md'),
            ]);
    }
}
