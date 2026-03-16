<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Reusable attribute names (e.g., "ფრეონის ტიპი", "სიმძლავრე")
        Schema::create('spec_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Georgian name
            $table->string('name_en')->nullable();
            $table->string('name_ru')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        // Predefined values for each attribute (e.g., R134a, R404a for ფრეონის ტიპი)
        Schema::create('spec_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spec_attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value');
            $table->timestamps();

            $table->index('spec_attribute_id');
        });

        // Product ↔ attribute ↔ value pivot
        Schema::create('product_specs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->foreignId('spec_attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value'); // The actual value (may or may not match a predefined one)
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_specs');
        Schema::dropIfExists('spec_attribute_values');
        Schema::dropIfExists('spec_attributes');
    }
};
