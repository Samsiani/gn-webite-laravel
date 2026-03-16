<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpecAttributeResource\Pages;
use App\Models\SpecAttribute;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SpecAttributeResource extends Resource
{
    protected static ?string $model = SpecAttribute::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'spec-attributes';

    public static function getNavigationLabel(): string
    {
        return 'Attributes';
    }

    public static function getModelLabel(): string
    {
        return 'Attribute';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Attribute')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Name (Georgian)')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('name_en')
                        ->label('Name (English)')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('name_ru')
                        ->label('Name (Russian)')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('position')
                        ->label('Sort Order')
                        ->numeric()
                        ->default(0),
                ])
                ->columns(2),

            Forms\Components\Section::make('Predefined Values')
                ->schema([
                    Forms\Components\Repeater::make('values')
                        ->relationship()
                        ->schema([
                            Forms\Components\TextInput::make('value')
                                ->label('Value')
                                ->required()
                                ->maxLength(255),
                        ])
                        ->addActionLabel('Add Value')
                        ->defaultItems(0)
                        ->reorderable(false)
                        ->hiddenLabel()
                        ->columns(1),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name (KA)')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name_en')
                    ->label('Name (EN)')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_ru')
                    ->label('Name (RU)')
                    ->searchable(),
                Tables\Columns\TextColumn::make('values_count')
                    ->label('Values')
                    ->counts('values')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_specs_count')
                    ->label('Used in')
                    ->counts('productSpecs')
                    ->suffix(' products')
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->label('Order')
                    ->sortable(),
            ])
            ->defaultSort('position')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSpecAttributes::route('/'),
            'create' => Pages\CreateSpecAttribute::route('/create'),
            'edit' => Pages\EditSpecAttribute::route('/{record}/edit'),
        ];
    }
}
