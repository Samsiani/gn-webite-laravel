<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;

class CreateCollectionDescription extends Command
{
    protected $signature = 'gn:create-collection-description';
    protected $description = 'Create the description attribute for collections (rich text, translatable)';

    public function handle(): int
    {
        // Find or create the collection attribute group
        $group = AttributeGroup::firstOrCreate(
            ['attributable_type' => 'collection', 'handle' => 'collection_details'],
            ['name' => ['en' => 'Details', 'ka' => 'დეტალები'], 'position' => 1]
        );

        $this->info("Attribute group: {$group->handle} (ID: {$group->id})");

        // Ensure 'name' attribute exists
        Attribute::firstOrCreate(
            ['attribute_group_id' => $group->id, 'handle' => 'name'],
            [
                'attribute_type' => 'collection',
                'name' => ['en' => 'Name', 'ka' => 'სახელი'],
                'type' => \Lunar\FieldTypes\TranslatedText::class,
                'position' => 1,
                'required' => true,
                'system' => true,
                'section' => 'main',
                'configuration' => ['richtext' => false],
            ]
        );

        $this->info('Name attribute ensured.');

        // Create 'description' attribute
        $desc = Attribute::firstOrCreate(
            ['attribute_group_id' => $group->id, 'handle' => 'description'],
            [
                'attribute_type' => 'collection',
                'name' => ['en' => 'Description', 'ka' => 'აღწერა'],
                'type' => \Lunar\FieldTypes\TranslatedText::class,
                'position' => 2,
                'required' => false,
                'system' => false,
                'section' => 'main',
                'configuration' => ['richtext' => true],
            ]
        );

        $this->info("Description attribute: {$desc->handle} (ID: {$desc->id}, richtext: true)");
        $this->info('Done! Refresh /admin/collections to see the description editor.');

        return self::SUCCESS;
    }
}
