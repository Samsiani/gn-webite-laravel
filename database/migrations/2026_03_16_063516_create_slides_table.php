<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->string('title_ru')->nullable();
            $table->text('subtitle')->nullable();
            $table->text('subtitle_en')->nullable();
            $table->text('subtitle_ru')->nullable();
            $table->string('badge')->nullable();
            $table->string('badge_en')->nullable();
            $table->string('badge_ru')->nullable();
            $table->string('cta_text')->nullable();
            $table->string('cta_text_en')->nullable();
            $table->string('cta_text_ru')->nullable();
            $table->string('cta_url')->nullable();
            $table->string('cta2_text')->nullable();
            $table->string('cta2_text_en')->nullable();
            $table->string('cta2_text_ru')->nullable();
            $table->string('cta2_url')->nullable();
            $table->string('bg_type')->default('gradient'); // gradient, image
            $table->string('bg_gradient')->nullable(); // e.g. "from-primary via-primary-dark to-[#2d2f5e]"
            $table->string('overlay_color')->nullable(); // e.g. "rgba(26,28,61,0.85)"
            $table->boolean('show_stats')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slides');
    }
};
