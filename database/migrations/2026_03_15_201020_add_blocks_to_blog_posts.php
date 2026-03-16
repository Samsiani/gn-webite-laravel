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
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->json('blocks')->nullable()->after('content_ru');
            $table->json('blocks_en')->nullable()->after('blocks');
            $table->json('blocks_ru')->nullable()->after('blocks_en');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn(['blocks', 'blocks_en', 'blocks_ru']);
        });
    }
};
