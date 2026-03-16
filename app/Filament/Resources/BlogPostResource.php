<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Blocks\BlogBlocks;
use Illuminate\Support\Str;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Blog';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'blog/posts';

    public static function getNavigationLabel(): string
    {
        return 'Posts';
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) BlogPost::where('status', 'draft')->count() ?: null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Left column
            Forms\Components\Group::make()->schema([
                // Georgian content
                Forms\Components\Section::make('Content (Georgian)')->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Title')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                            $operation === 'create' ? $set('slug', Str::slug($state)) : null
                        ),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Forms\Components\Textarea::make('excerpt')
                        ->label('Excerpt')
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\Builder::make('blocks')
                        ->label('Content Blocks')
                        ->blocks(BlogBlocks::all())
                        ->columnSpanFull()
                        ->collapsible()
                        ->reorderableWithButtons()
                        ->addActionLabel('Add Block'),
                ])->columns(2),

                // English
                Forms\Components\Section::make('Content (English)')->schema([
                    Forms\Components\TextInput::make('title_en')
                        ->label('Title')
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                            $operation === 'create' ? $set('slug_en', Str::slug($state)) : null
                        ),
                    Forms\Components\TextInput::make('slug_en')
                        ->label('Slug')
                        ->unique(ignoreRecord: true),
                    Forms\Components\Textarea::make('excerpt_en')
                        ->label('Excerpt')
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\Builder::make('blocks_en')
                        ->label('Content Blocks')
                        ->blocks(BlogBlocks::all())
                        ->columnSpanFull()
                        ->collapsible()
                        ->reorderableWithButtons()
                        ->addActionLabel('Add Block'),
                ])->columns(2)->collapsible()->collapsed(),

                // Russian
                Forms\Components\Section::make('Content (Russian)')->schema([
                    Forms\Components\TextInput::make('title_ru')
                        ->label('Title')
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) =>
                            $operation === 'create' ? $set('slug_ru', Str::slug($state)) : null
                        ),
                    Forms\Components\TextInput::make('slug_ru')
                        ->label('Slug')
                        ->unique(ignoreRecord: true),
                    Forms\Components\Textarea::make('excerpt_ru')
                        ->label('Excerpt')
                        ->rows(3)
                        ->columnSpanFull(),
                    Forms\Components\Builder::make('blocks_ru')
                        ->label('Content Blocks')
                        ->blocks(BlogBlocks::all())
                        ->columnSpanFull()
                        ->collapsible()
                        ->reorderableWithButtons()
                        ->addActionLabel('Add Block'),
                ])->columns(2)->collapsible()->collapsed(),

                // SEO
                Forms\Components\Section::make('SEO')->schema([
                    Forms\Components\TextInput::make('meta_title')->label('Meta Title (KA)'),
                    Forms\Components\TextInput::make('meta_title_en')->label('Meta Title (EN)'),
                    Forms\Components\TextInput::make('meta_title_ru')->label('Meta Title (RU)'),
                    Forms\Components\TextInput::make('meta_description')->label('Meta Description (KA)'),
                    Forms\Components\TextInput::make('meta_description_en')->label('Meta Description (EN)'),
                    Forms\Components\TextInput::make('meta_description_ru')->label('Meta Description (RU)'),
                ])->columns(3)->collapsible()->collapsed(),
            ])->columnSpan(['lg' => 2]),

            // Right sidebar
            Forms\Components\Group::make()->schema([
                Forms\Components\Section::make('Publish')->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'published' => 'Published',
                        ])
                        ->default('draft')
                        ->required(),
                    Forms\Components\DateTimePicker::make('published_at')
                        ->label('Publish Date')
                        ->default(now()),
                ])->compact(),

                Forms\Components\Section::make('Featured Image')->schema([
                    SpatieMediaLibraryFileUpload::make('featured')
                        ->collection('featured')
                        ->image()
                        ->imageEditor()
                        ->hiddenLabel(),
                ])->compact(),

                Forms\Components\Section::make('Category')->schema([
                    Forms\Components\Select::make('blog_category_id')
                        ->label('')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->label('Name (KA)')->required(),
                            Forms\Components\TextInput::make('name_en')->label('Name (EN)'),
                            Forms\Components\TextInput::make('name_ru')->label('Name (RU)'),
                            Forms\Components\TextInput::make('slug')->required(),
                        ]),
                ])->compact(),

                Forms\Components\Section::make('Tags')->schema([
                    Forms\Components\TagsInput::make('post_tags')
                        ->label('')
                        ->splitKeys(['Tab', ','])
                        ->helperText('Press Tab or comma to add'),
                ])->collapsible()->collapsed(),
            ])->columnSpan(['lg' => 1]),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('featured')
                    ->collection('featured')
                    ->square()
                    ->label(''),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                    ]),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Date')
                    ->date('d.m.Y')
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
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
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }

    // Handle tags on save
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        return self::handleTags($data);
    }

    private static function handleTags(array $data): array
    {
        // Tags are handled in afterCreate/afterSave
        unset($data['post_tags']);
        return $data;
    }
}
