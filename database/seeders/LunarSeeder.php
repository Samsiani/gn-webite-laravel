<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Lunar\Admin\Models\Staff;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Channel;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\ProductType;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRate;
use Lunar\Models\TaxZone;

class LunarSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedLanguages();
        $this->seedCurrency();
        $this->seedChannel();
        $this->seedCountry();
        $this->seedTax();
        $this->seedCustomerGroup();
        $this->seedProductType();
        $this->seedCollectionGroup();
        $this->seedStaff();
    }

    private function seedLanguages(): void
    {
        $languages = [
            ['code' => 'ka', 'name' => 'ქართული', 'default' => true],
            ['code' => 'en', 'name' => 'English', 'default' => false],
            ['code' => 'ru', 'name' => 'Русский', 'default' => false],
        ];

        foreach ($languages as $lang) {
            Language::firstOrCreate(
                ['code' => $lang['code']],
                ['name' => $lang['name'], 'default' => $lang['default']]
            );
        }
    }

    private function seedCurrency(): void
    {
        Currency::firstOrCreate(
            ['code' => 'GEL'],
            [
                'name' => 'Georgian Lari',
                'exchange_rate' => 1,
                'decimal_places' => 2,
                'default' => true,
                'enabled' => true,
            ]
        );
    }

    private function seedChannel(): void
    {
        Channel::firstOrCreate(
            ['handle' => 'webstore'],
            [
                'name' => 'GN Industrial Webstore',
                'default' => true,
                'url' => 'https://gn.ge',
            ]
        );
    }

    private function seedCountry(): void
    {
        Country::firstOrCreate(
            ['iso3' => 'GEO'],
            [
                'name' => 'Georgia',
                'iso2' => 'GE',
                'phonecode' => '+995',
                'capital' => 'Tbilisi',
                'currency' => 'GEL',
                'native' => 'საქართველო',
                'emoji' => '🇬🇪',
                'emoji_u' => 'U+1F1EC U+1F1EA',
            ]
        );
    }

    private function seedTax(): void
    {
        $taxClass = TaxClass::firstOrCreate(
            ['name' => 'VAT'],
            ['default' => true]
        );

        $taxZone = TaxZone::firstOrCreate(
            ['name' => 'Georgia'],
            [
                'zone_type' => 'country',
                'default' => true,
                'active' => true,
                'price_display' => 'include_tax',
            ]
        );

        // Link zone to country
        $country = Country::where('iso2', 'GE')->first();
        if ($country) {
            DB::table('lunar_tax_zone_countries')->insertOrIgnore([
                'tax_zone_id' => $taxZone->id,
                'country_id' => $country->id,
            ]);
        }

        $taxRate = TaxRate::firstOrCreate(
            ['tax_zone_id' => $taxZone->id, 'name' => 'Georgian VAT'],
            ['priority' => 1]
        );

        DB::table('lunar_tax_rate_amounts')->insertOrIgnore([
            'tax_rate_id' => $taxRate->id,
            'tax_class_id' => $taxClass->id,
            'percentage' => 18.00,
        ]);
    }

    private function seedCustomerGroup(): void
    {
        CustomerGroup::firstOrCreate(
            ['handle' => 'retail'],
            [
                'name' => 'Retail',
                'default' => true,
            ]
        );

        CustomerGroup::firstOrCreate(
            ['handle' => 'wholesale'],
            ['name' => 'Wholesale', 'default' => false]
        );
    }

    private function seedProductType(): void
    {
        $productType = ProductType::firstOrCreate(
            ['name' => 'Kitchen Equipment'],
        );

        // Content attribute group
        $contentGroup = AttributeGroup::firstOrCreate(
            ['handle' => 'content'],
            [
                'name' => ['ka' => 'შინაარსი', 'en' => 'Content', 'ru' => 'Содержание'],
                'position' => 1,
                'attributable_type' => \Lunar\Models\Product::class,
            ]
        );

        $this->createAttribute($contentGroup, 'name', 'Name', [
            'ka' => 'სახელი', 'en' => 'Name', 'ru' => 'Название',
        ], 'Lunar\\FieldTypes\\TranslatedText', true, 1);

        $this->createAttribute($contentGroup, 'description', 'Description', [
            'ka' => 'აღწერა', 'en' => 'Description', 'ru' => 'Описание',
        ], 'Lunar\\FieldTypes\\TranslatedText', false, 2, 'richtext');

        $this->createAttribute($contentGroup, 'short_description', 'Short Description', [
            'ka' => 'მოკლე აღწერა', 'en' => 'Short Description', 'ru' => 'Краткое описание',
        ], 'Lunar\\FieldTypes\\TranslatedText', false, 3);

        // General Specs attribute group
        $specsGroup = AttributeGroup::firstOrCreate(
            ['handle' => 'general_specs'],
            [
                'name' => ['ka' => 'ძირითადი მახასიათებლები', 'en' => 'General Specifications', 'ru' => 'Основные характеристики'],
                'position' => 2,
                'attributable_type' => \Lunar\Models\Product::class,
            ]
        );

        $this->createAttribute($specsGroup, 'brand', 'Brand', [
            'ka' => 'ბრენდი', 'en' => 'Brand', 'ru' => 'Бренд',
        ], 'Lunar\\FieldTypes\\Text', false, 1, 'text', true);

        $this->createAttribute($specsGroup, 'control_type', 'Control Type', [
            'ka' => 'კონტროლი', 'en' => 'Control Type', 'ru' => 'Тип управления',
        ], 'Lunar\\FieldTypes\\Text', false, 2, 'text', true);

        $this->createAttribute($specsGroup, 'body_material', 'Body Material', [
            'ka' => 'კორპუსი', 'en' => 'Body Material', 'ru' => 'Материал корпуса',
        ], 'Lunar\\FieldTypes\\Text', false, 3, 'text', true);

        $this->createAttribute($specsGroup, 'power_source', 'Power Source', [
            'ka' => 'კვების წყარო', 'en' => 'Power Source', 'ru' => 'Источник питания',
        ], 'Lunar\\FieldTypes\\Text', false, 4, 'text', true);

        $this->createAttribute($specsGroup, 'gas_consumption', 'Gas Consumption', [
            'ka' => 'გაზის მოხმარება', 'en' => 'Gas Consumption', 'ru' => 'Расход газа',
        ], 'Lunar\\FieldTypes\\Text', false, 5, 'text', true);

        // Technical Details attribute group
        $techGroup = AttributeGroup::firstOrCreate(
            ['handle' => 'technical_details'],
            [
                'name' => ['ka' => 'ტექნიკური დეტალები', 'en' => 'Technical Details', 'ru' => 'Технические детали'],
                'position' => 3,
                'attributable_type' => \Lunar\Models\Product::class,
            ]
        );

        $this->createAttribute($techGroup, 'power', 'Power', [
            'ka' => 'სიმძლავრე', 'en' => 'Power', 'ru' => 'Мощность',
        ], 'Lunar\\FieldTypes\\Text', false, 1);

        $this->createAttribute($techGroup, 'voltage', 'Voltage', [
            'ka' => 'ძაბვა', 'en' => 'Voltage', 'ru' => 'Напряжение',
        ], 'Lunar\\FieldTypes\\Text', false, 2);

        $this->createAttribute($techGroup, 'dimensions', 'Dimensions', [
            'ka' => 'ზომები', 'en' => 'Dimensions', 'ru' => 'Размеры',
        ], 'Lunar\\FieldTypes\\Text', false, 3);

        $this->createAttribute($techGroup, 'weight', 'Weight', [
            'ka' => 'წონა', 'en' => 'Weight', 'ru' => 'Вес',
        ], 'Lunar\\FieldTypes\\Text', false, 4);

        $this->createAttribute($techGroup, 'capacity', 'Capacity', [
            'ka' => 'ტევადობა', 'en' => 'Capacity', 'ru' => 'Вместимость',
        ], 'Lunar\\FieldTypes\\Text', false, 5);

        // SEO attribute group
        $seoGroup = AttributeGroup::firstOrCreate(
            ['handle' => 'seo'],
            [
                'name' => ['ka' => 'SEO', 'en' => 'SEO', 'ru' => 'SEO'],
                'position' => 4,
                'attributable_type' => \Lunar\Models\Product::class,
            ]
        );

        $this->createAttribute($seoGroup, 'meta_title', 'Meta Title', [
            'ka' => 'მეტა სათაური', 'en' => 'Meta Title', 'ru' => 'Мета заголовок',
        ], 'Lunar\\FieldTypes\\TranslatedText', false, 1);

        $this->createAttribute($seoGroup, 'meta_description', 'Meta Description', [
            'ka' => 'მეტა აღწერა', 'en' => 'Meta Description', 'ru' => 'Мета описание',
        ], 'Lunar\\FieldTypes\\TranslatedText', false, 2);

        // Attach all attributes to the product type
        $attributes = Attribute::all();
        $productType->mappedAttributes()->sync($attributes->pluck('id'));
    }

    private function createAttribute(
        AttributeGroup $group,
        string $handle,
        string $name,
        array $translatedName,
        string $fieldType,
        bool $required = false,
        int $position = 1,
        string $type = 'text',
        bool $filterable = false,
    ): void {
        $existing = Attribute::where('handle', $handle)
            ->where('attribute_group_id', $group->id)
            ->first();

        if ($existing) {
            return;
        }

        $attribute = new Attribute;
        $attribute->attribute_type = $group->attributable_type;
        $attribute->handle = $handle;
        $attribute->attribute_group_id = $group->id;
        $attribute->name = $translatedName;
        $attribute->position = $position;
        $attribute->section = null;
        $attribute->type = $fieldType;
        $attribute->required = $required;
        $attribute->configuration = ['type' => $type];
        $attribute->filterable = $filterable;
        $attribute->searchable = in_array($handle, ['name', 'description', 'brand']);
        $attribute->system = in_array($handle, ['name']);
        $attribute->validation_rules = $required ? json_encode(['required' => 'true']) : null;
        $attribute->save();
    }

    private function seedCollectionGroup(): void
    {
        CollectionGroup::firstOrCreate(
            ['handle' => 'product-categories'],
            ['name' => 'Product Categories']
        );
    }

    private function seedStaff(): void
    {
        Staff::firstOrCreate(
            ['email' => 'admin@gn.ge'],
            [
                'first_name' => 'Admin',
                'last_name' => 'GN',
                'admin' => true,
                'password' => bcrypt('password'),
            ]
        );
    }
}
