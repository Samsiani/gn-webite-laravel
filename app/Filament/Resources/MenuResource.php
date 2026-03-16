<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Models\Menu;
use App\Models\MenuItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Models\Collection as LunarCollection;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;
    protected static ?string $navigationIcon = 'heroicon-o-bars-3';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 8;
    protected static ?string $slug = 'menus';

    public static function getNavigationLabel(): string
    {
        return 'Menus';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Menu')->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('handle')->required()->unique(ignoreRecord: true)
                    ->helperText('e.g. "main", "footer"'),
            ])->columns(2),

            Forms\Components\Section::make('Menu Items')->schema([
                Forms\Components\Repeater::make('items')
                    ->relationship()
                    ->schema([
                        Forms\Components\Grid::make(6)->schema([
                            Forms\Components\TextInput::make('label')
                                ->label('Label (KA)')
                                ->required()
                                ->columnSpan(2),
                            Forms\Components\TextInput::make('label_en')
                                ->label('Label (EN)')
                                ->columnSpan(2),
                            Forms\Components\TextInput::make('label_ru')
                                ->label('Label (RU)')
                                ->columnSpan(2),
                        ]),
                        Forms\Components\Grid::make(4)->schema([
                            Forms\Components\Select::make('type')
                                ->options([
                                    'custom' => 'Custom URL',
                                    'category' => 'Product Category',
                                    'page' => 'Page',
                                ])
                                ->default('custom')
                                ->live()
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('url')
                                ->label('URL')
                                ->placeholder('/about')
                                ->visible(fn (Forms\Get $get) => $get('type') === 'custom' || ! $get('type'))
                                ->columnSpan(1),
                            Forms\Components\Select::make('reference_id')
                                ->label('Category')
                                ->options(fn () => LunarCollection::all()->mapWithKeys(fn ($c) => [
                                    $c->id => $c->translateAttribute('name') ?? "#{$c->id}",
                                ]))
                                ->searchable()
                                ->visible(fn (Forms\Get $get) => $get('type') === 'category')
                                ->columnSpan(1),
                            Forms\Components\Toggle::make('is_active')
                                ->label('Active')
                                ->default(true)
                                ->columnSpan(1),
                            Forms\Components\Toggle::make('open_new_tab')
                                ->label('New Tab')
                                ->default(false)
                                ->columnSpan(1),
                        ]),
                        // Children (sub-menu items)
                        Forms\Components\Repeater::make('children')
                            ->relationship()
                            ->schema([
                                Forms\Components\Grid::make(5)->schema([
                                    Forms\Components\TextInput::make('label')
                                        ->label('Label (KA)')
                                        ->required(),
                                    Forms\Components\TextInput::make('label_en')
                                        ->label('EN'),
                                    Forms\Components\TextInput::make('label_ru')
                                        ->label('RU'),
                                    Forms\Components\Select::make('type')
                                        ->options(['custom' => 'URL', 'category' => 'Category'])
                                        ->default('custom')
                                        ->live(),
                                    Forms\Components\TextInput::make('url')
                                        ->placeholder('/page')
                                        ->visible(fn (Forms\Get $get) => $get('type') !== 'category'),
                                    Forms\Components\Select::make('reference_id')
                                        ->label('Category')
                                        ->options(fn () => LunarCollection::all()->mapWithKeys(fn ($c) => [
                                            $c->id => $c->translateAttribute('name') ?? "#{$c->id}",
                                        ]))
                                        ->searchable()
                                        ->visible(fn (Forms\Get $get) => $get('type') === 'category'),
                                    Forms\Components\Toggle::make('is_active')->default(true),
                                ]),
                            ])
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data, Forms\Get $get): array {
                                $data['menu_id'] = $get('../../id') ?? 0;
                                return $data;
                            })
                            ->addActionLabel('Add Sub-item')
                            ->defaultItems(0)
                            ->collapsible()
                            ->collapsed()
                            ->label('Sub-menu Items'),
                    ])
                    ->reorderable()
                    ->reorderableWithButtons()
                    ->collapsible()
                    ->cloneable()
                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Menu Item')
                    ->addActionLabel('Add Menu Item')
                    ->defaultItems(0),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable(),
                Tables\Columns\TextColumn::make('handle')->badge(),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Items'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
