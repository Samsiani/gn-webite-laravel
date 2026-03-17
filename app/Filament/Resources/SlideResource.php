<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SlideResource\Pages;
use App\Models\Slide;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SlideResource extends Resource
{
    protected static ?string $model = Slide::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 7;
    protected static ?string $slug = 'slides';

    public static function getNavigationLabel(): string
    {
        return 'Slider';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->schema([
                // Georgian
                Forms\Components\Section::make('Content (Georgian)')->schema([
                    Forms\Components\TextInput::make('title')->label('Title')->required(),
                    Forms\Components\TextInput::make('badge')->label('Badge')->placeholder('e.g. Authorized Dealer'),
                    Forms\Components\Textarea::make('subtitle')->label('Subtitle')->rows(2),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('cta_text')->label('Button 1 Text')->placeholder('Browse Products'),
                        Forms\Components\TextInput::make('cta_url')->label('Button 1 URL')->placeholder('/shop'),
                    ]),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('cta2_text')->label('Button 2 Text')->placeholder('Contact Us'),
                        Forms\Components\TextInput::make('cta2_url')->label('Button 2 URL')->placeholder('tel:+995...'),
                    ]),
                ]),

                // English
                Forms\Components\Section::make('Content (English)')->schema([
                    Forms\Components\TextInput::make('title_en')->label('Title'),
                    Forms\Components\TextInput::make('badge_en')->label('Badge'),
                    Forms\Components\Textarea::make('subtitle_en')->label('Subtitle')->rows(2),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('cta_text_en')->label('Button 1 Text'),
                        Forms\Components\TextInput::make('cta2_text_en')->label('Button 2 Text'),
                    ]),
                ])->collapsible()->collapsed(),

                // Russian
                Forms\Components\Section::make('Content (Russian)')->schema([
                    Forms\Components\TextInput::make('title_ru')->label('Title'),
                    Forms\Components\TextInput::make('badge_ru')->label('Badge'),
                    Forms\Components\Textarea::make('subtitle_ru')->label('Subtitle')->rows(2),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('cta_text_ru')->label('Button 1 Text'),
                        Forms\Components\TextInput::make('cta2_text_ru')->label('Button 2 Text'),
                    ]),
                ])->collapsible()->collapsed(),
            ])->columnSpan(['lg' => 2]),

            Forms\Components\Group::make()->schema([
                // Settings
                Forms\Components\Section::make('Settings')->schema([
                    Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
                    Forms\Components\TextInput::make('position')->label('Order')->numeric()->default(0),
                ])->compact(),

                // Stats Blocks
                Forms\Components\Section::make('Stats Blocks (right side)')->schema([
                    Forms\Components\Toggle::make('show_stats')->label('Show Stats')->default(false)->live(),
                    Forms\Components\Repeater::make('stats')
                        ->schema([
                            Forms\Components\TextInput::make('value')
                                ->label('Value')
                                ->placeholder('20,000+')
                                ->required(),
                            Forms\Components\TextInput::make('label')
                                ->label('Label (KA)')
                                ->placeholder('პროდუქტი კატალოგში')
                                ->required(),
                            Forms\Components\TextInput::make('label_en')
                                ->label('EN'),
                            Forms\Components\TextInput::make('label_ru')
                                ->label('RU'),
                        ])
                        ->columns(2)
                        ->defaultItems(0)
                        ->maxItems(4)
                        ->addActionLabel('Add Stat')
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => ($state['value'] ?? '') . ' — ' . ($state['label'] ?? ''))
                        ->visible(fn (Forms\Get $get) => $get('show_stats')),
                ])->compact(),

                // Background
                Forms\Components\Section::make('Background')->schema([
                    Forms\Components\Select::make('bg_type')
                        ->label('Type')
                        ->options([
                            'gradient' => 'Gradient',
                            'image' => 'Image',
                        ])
                        ->default('gradient')
                        ->live()
                        ->required(),
                    Forms\Components\TextInput::make('bg_gradient')
                        ->label('Gradient Classes')
                        ->placeholder('from-primary via-primary-dark to-[#2d2f5e]')
                        ->visible(fn (Forms\Get $get) => $get('bg_type') === 'gradient')
                        ->helperText('Tailwind gradient classes'),
                    SpatieMediaLibraryFileUpload::make('background')
                        ->collection('background')
                        ->image()
                        ->imageEditor()
                        ->visible(fn (Forms\Get $get) => $get('bg_type') === 'image')
                        ->hiddenLabel(),
                    Forms\Components\TextInput::make('overlay_color')
                        ->label('Overlay')
                        ->placeholder('rgba(26,28,61,0.85)')
                        ->visible(fn (Forms\Get $get) => $get('bg_type') === 'image')
                        ->helperText('Type "transparent" or "none" for clean image without overlay'),
                ]),
            ])->columnSpan(['lg' => 1]),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('position')->label('#')->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')->label('Active'),
                Tables\Columns\TextColumn::make('title')->limit(40)->searchable(),
                Tables\Columns\TextColumn::make('bg_type')->badge()->label('BG')
                    ->colors(['primary' => 'gradient', 'success' => 'image']),
                Tables\Columns\IconColumn::make('show_stats')->boolean()->label('Stats'),
                Tables\Columns\TextColumn::make('cta_text')->label('CTA')->limit(20),
            ])
            ->defaultSort('position')
            ->reorderable('position')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSlides::route('/'),
            'create' => Pages\CreateSlide::route('/create'),
            'edit' => Pages\EditSlide::route('/{record}/edit'),
        ];
    }
}
