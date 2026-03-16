<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Models\ProductSpec;
use App\Models\SpecAttribute;
use App\Models\SpecAttributeValue;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Forms\Components\Attributes;
use Lunar\Admin\Support\Forms\Components\Tags as TagsComponent;
use Lunar\Admin\Support\Pages\BaseEditRecord;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\Currency;
use Lunar\Models\Tag;

class EditProductSinglePage extends BaseEditRecord
{
    protected static string $resource = ProductResource::class;

    public static bool $formActionsAreSticky = true;

    public function getTitle(): string
    {
        return __('Edit Product');
    }

    public static function getNavigationLabel(): string
    {
        return __('Edit');
    }

    public function form(Form $form): Form
    {
        $currency = Currency::getDefault();

        return $form
            ->schema([
                // ── LEFT COLUMN ──
                Forms\Components\Group::make()
                    ->schema([
                        // Product Type
                        Forms\Components\Section::make(__('Product Type'))
                            ->schema([
                                Forms\Components\Select::make('product_type_id')
                                    ->label(__('Product Type'))
                                    ->relationship('productType', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live(),
                            ])
                            ->compact(),

                        // Pricing & Inventory
                        Forms\Components\Section::make(__('Pricing & Inventory'))
                            ->schema([
                                Forms\Components\TextInput::make('compare_price')
                                    ->label(__('Regular Price'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix($currency->code ?? 'GEL')
                                    ->helperText(__('Original price (strikethrough)')),
                                Forms\Components\TextInput::make('base_price')
                                    ->label(__('Sale Price'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->required()
                                    ->prefix($currency->code ?? 'GEL')
                                    ->helperText(__('Current selling price')),
                                Forms\Components\TextInput::make('variant_sku')
                                    ->label('SKU')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('variant_stock')
                                    ->label(__('Stock'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->integer(),
                            ])
                            ->columns(4),

                        // Dimensions & Weight
                        Forms\Components\Section::make(__('Dimensions (CM) & Weight (KG)'))
                            ->schema([
                                Forms\Components\TextInput::make('dimension_length')
                                    ->label(__('Length'))
                                    ->numeric()->minValue(0)->suffix('cm'),
                                Forms\Components\TextInput::make('dimension_width')
                                    ->label(__('Width'))
                                    ->numeric()->minValue(0)->suffix('cm'),
                                Forms\Components\TextInput::make('dimension_height')
                                    ->label(__('Height'))
                                    ->numeric()->minValue(0)->suffix('cm'),
                                Forms\Components\TextInput::make('variant_weight')
                                    ->label(__('Weight'))
                                    ->numeric()->minValue(0)->suffix('kg'),
                            ])
                            ->columns(4)
                            ->compact(),

                        // Content attributes (name, description, short_description)
                        Attributes::make()->statePath('attribute_data'),

                        // ── Specifications Repeater (WooCommerce-style) ──
                        Forms\Components\Section::make(__('Attributes'))
                            ->schema([
                                Forms\Components\Repeater::make('specs')
                                    ->schema([
                                        Forms\Components\Select::make('spec_attribute_id')
                                            ->label(__('Attribute'))
                                            ->options(SpecAttribute::orderBy('position')->pluck('name', 'id'))
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('name')
                                                    ->label(__('Name (Georgian)'))
                                                    ->required(),
                                                Forms\Components\TextInput::make('name_en')
                                                    ->label(__('Name (English)')),
                                                Forms\Components\TextInput::make('name_ru')
                                                    ->label(__('Name (Russian)')),
                                            ])
                                            ->createOptionUsing(function (array $data): int {
                                                $attr = SpecAttribute::create([
                                                    'name' => $data['name'],
                                                    'name_en' => $data['name_en'] ?? null,
                                                    'name_ru' => $data['name_ru'] ?? null,
                                                    'position' => SpecAttribute::max('position') + 1,
                                                ]);
                                                return $attr->id;
                                            })
                                            ->columnSpan(1),

                                        Forms\Components\Select::make('value')
                                            ->label(__('Value'))
                                            ->options(function (Forms\Get $get) {
                                                $attrId = $get('spec_attribute_id');
                                                if (! $attrId) return [];
                                                return SpecAttributeValue::where('spec_attribute_id', $attrId)
                                                    ->pluck('value', 'value')
                                                    ->toArray();
                                            })
                                            ->searchable()
                                            ->required()
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('value')
                                                    ->label(__('Value'))
                                                    ->required(),
                                            ])
                                            ->createOptionUsing(function (array $data, Forms\Get $get): string {
                                                $attrId = $get('spec_attribute_id');
                                                if ($attrId) {
                                                    SpecAttributeValue::firstOrCreate([
                                                        'spec_attribute_id' => $attrId,
                                                        'value' => $data['value'],
                                                    ]);
                                                }
                                                return $data['value'];
                                            })
                                            ->columnSpan(1),
                                    ])
                                    ->columns(2)
                                    ->reorderable()
                                    ->reorderableWithButtons()
                                    ->collapsible()
                                    ->cloneable()
                                    ->itemLabel(function (array $state): ?string {
                                        $attr = isset($state['spec_attribute_id'])
                                            ? SpecAttribute::find($state['spec_attribute_id'])
                                            : null;
                                        $name = $attr?->name ?? '...';
                                        $val = $state['value'] ?? '';
                                        return $val ? "{$name}: {$val}" : $name;
                                    })
                                    ->addActionLabel(__('Add Attribute'))
                                    ->defaultItems(0)
                                    ->hiddenLabel(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                // ── RIGHT COLUMN (sidebar) ──
                Forms\Components\Group::make()
                    ->schema([
                        // Publish
                        Forms\Components\Section::make(__('Publish'))
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label(__('Status'))
                                    ->options([
                                        'draft' => __('Draft'),
                                        'published' => __('Published'),
                                    ])
                                    ->default('draft')
                                    ->required(),
                            ])
                            ->compact(),

                        // Featured Image
                        Forms\Components\Section::make(__('Product Image'))
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('featured_image')
                                    ->collection(config('lunar.media.collection', 'images'))
                                    ->image()
                                    ->imageEditor()
                                    ->imagePreviewHeight('200')
                                    ->imageCropAspectRatio('1:1')
                                    ->imageResizeTargetWidth('800')
                                    ->imageResizeTargetHeight('800')
                                    ->hiddenLabel()
                                    ->helperText(__('Main product image (1:1)')),
                            ])
                            ->compact(),

                        // Gallery
                        Forms\Components\Section::make(__('Product Gallery'))
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('gallery')
                                    ->collection('gallery')
                                    ->multiple()
                                    ->maxFiles(10)
                                    ->reorderable()
                                    ->image()
                                    ->imagePreviewHeight('80')
                                    ->panelLayout('grid')
                                    ->columns(3)
                                    ->hiddenLabel(),
                            ])
                            ->collapsible(),

                        // Categories
                        Forms\Components\Section::make(__('Categories'))
                            ->schema([
                                Forms\Components\Select::make('collection_ids')
                                    ->label('')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->options(fn () => LunarCollection::all()->mapWithKeys(fn ($c) => [
                                        $c->id => $c->translateAttribute('name') ?? 'Collection #' . $c->id,
                                    ])),
                            ])
                            ->compact(),

                        // Brand
                        Forms\Components\Section::make(__('Brand'))
                            ->schema([
                                Forms\Components\Select::make('brand_id')
                                    ->label('')
                                    ->relationship('brand', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')->required(),
                                    ]),
                            ])
                            ->compact(),

                        // Tags
                        Forms\Components\Section::make(__('Tags'))
                            ->schema([
                                TagsComponent::make('tags')
                                    ->suggestions(Tag::all()->pluck('value')->all())
                                    ->splitKeys(['Tab', ','])
                                    ->label('')
                                    ->helperText(__('Press Tab or comma to add')),
                            ])
                            ->collapsible()
                            ->collapsed(),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        // Variant data
        $variant = $record->variants()->first();
        if ($variant) {
            $data['variant_sku'] = $variant->sku;
            $data['variant_stock'] = $variant->stock;
            $data['dimension_length'] = $variant->length_value;
            $data['dimension_width'] = $variant->width_value;
            $data['dimension_height'] = $variant->height_value;
            $data['variant_weight'] = $variant->weight_value;

            $price = $variant->prices()->first();
            if ($price) {
                $data['base_price'] = $price->price->value / 100;
                $data['compare_price'] = ($price->compare_price && $price->compare_price->value > 0)
                    ? $price->compare_price->value / 100 : null;
            }
        }

        // Collections
        $data['collection_ids'] = $record->collections()->pluck('lunar_collections.id')->toArray();

        // Specs repeater
        $data['specs'] = ProductSpec::where('product_id', $record->id)
            ->orderBy('position')
            ->get()
            ->map(fn ($s) => [
                'spec_attribute_id' => $s->spec_attribute_id,
                'value' => $s->value,
            ])
            ->toArray();

        return $data;
    }

    public function afterSave(): void
    {
        $record = $this->getRecord();
        $data = $this->form->getState();

        // Update variant
        $variant = $record->variants()->first();
        if (! $variant) {
            $variant = $record->variants()->create([
                'tax_class_id' => 1,
                'sku' => $data['variant_sku'] ?? 'SKU-' . $record->id,
                'stock' => $data['variant_stock'] ?? 0,
            ]);
        } else {
            $variant->update([
                'sku' => $data['variant_sku'] ?? $variant->sku,
                'stock' => $data['variant_stock'] ?? $variant->stock,
                'length_value' => $data['dimension_length'] ?? null,
                'width_value' => $data['dimension_width'] ?? null,
                'height_value' => $data['dimension_height'] ?? null,
                'weight_value' => $data['variant_weight'] ?? null,
            ]);
        }

        // Update price
        if (isset($data['base_price'])) {
            $currency = Currency::getDefault();
            $priceData = [
                'price' => (int) round($data['base_price'] * 100),
                'priceable_type' => 'product_variant',
            ];
            $priceData['compare_price'] = ! empty($data['compare_price'])
                ? (int) round($data['compare_price'] * 100) : null;

            $variant->prices()->updateOrCreate(
                ['currency_id' => $currency->id, 'min_quantity' => 1],
                $priceData
            );
        }

        // Sync collections
        if (isset($data['collection_ids'])) {
            $record->collections()->sync(
                collect($data['collection_ids'])->mapWithKeys(fn ($id) => [$id => ['position' => 0]])->toArray()
            );
        }

        // Save specs — preserve existing translations (value_en, value_ru)
        $existingSpecs = ProductSpec::where('product_id', $record->id)
            ->get()
            ->keyBy(fn ($s) => $s->spec_attribute_id . ':' . $s->value);

        ProductSpec::where('product_id', $record->id)->delete();
        foreach ($data['specs'] ?? [] as $i => $spec) {
            if (! empty($spec['spec_attribute_id']) && ! empty($spec['value'])) {
                $key = $spec['spec_attribute_id'] . ':' . $spec['value'];
                $existing = $existingSpecs->get($key);

                ProductSpec::create([
                    'product_id' => $record->id,
                    'spec_attribute_id' => $spec['spec_attribute_id'],
                    'value' => $spec['value'],
                    'value_en' => $existing?->value_en,
                    'value_ru' => $existing?->value_ru,
                    'position' => $i,
                ]);

                // Save value for future reuse
                SpecAttributeValue::firstOrCreate([
                    'spec_attribute_id' => $spec['spec_attribute_id'],
                    'value' => $spec['value'],
                ]);
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_storefront')
                ->label(__('View on Site'))
                ->icon('heroicon-o-eye')
                ->url(function () {
                    $url = $this->getRecord()->urls()->where('default', true)->first();
                    return $url ? url('/product/' . $url->slug) : '#';
                })
                ->openUrlInNewTab()
                ->color('gray'),
            Actions\DeleteAction::make(),
        ];
    }
}
