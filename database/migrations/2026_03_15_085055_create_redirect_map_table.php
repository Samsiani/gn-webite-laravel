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
        Schema::create('redirect_map', function (Blueprint $table) {
            $table->id();
            $table->string('old_url', 500)->unique();
            $table->string('new_url', 500);
            $table->integer('status_code')->default(301);
            $table->timestamps();

        });

        Schema::create('wp_id_map', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 50); // product, category, customer
            $table->unsignedBigInteger('wp_id');
            $table->unsignedBigInteger('lunar_id');
            $table->string('lang', 5)->nullable();
            $table->timestamps();

            $table->unique(['entity_type', 'wp_id', 'lang']);
            $table->index(['entity_type', 'lunar_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redirect_map');
        Schema::dropIfExists('wp_id_map');
    }
};
