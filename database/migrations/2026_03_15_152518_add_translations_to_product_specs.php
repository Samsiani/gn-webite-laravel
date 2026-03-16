<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_specs', function (Blueprint $table) {
            $table->string('value_en')->nullable()->after('value');
            $table->string('value_ru')->nullable()->after('value_en');
        });
    }

    public function down(): void
    {
        Schema::table('product_specs', function (Blueprint $table) {
            $table->dropColumn(['value_en', 'value_ru']);
        });
    }
};
