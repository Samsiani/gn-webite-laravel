<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('name_ru')->nullable();
            $table->string('slug')->unique();
            $table->string('slug_en')->nullable();
            $table->string('slug_ru')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->string('title_ru')->nullable();
            $table->string('slug')->unique();
            $table->string('slug_en')->nullable()->unique();
            $table->string('slug_ru')->nullable()->unique();
            $table->text('excerpt')->nullable();
            $table->text('excerpt_en')->nullable();
            $table->text('excerpt_ru')->nullable();
            $table->longText('content')->nullable();
            $table->longText('content_en')->nullable();
            $table->longText('content_ru')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_title_en')->nullable();
            $table->string('meta_title_ru')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_description_en')->nullable();
            $table->string('meta_description_ru')->nullable();
            $table->string('status')->default('draft'); // draft, published
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('author_id')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('published_at');
        });

        Schema::create('blog_post_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
            $table->string('tag');
            $table->timestamps();

            $table->index('tag');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_tag');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_categories');
    }
};
