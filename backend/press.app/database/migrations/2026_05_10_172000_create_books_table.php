<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('book_categories')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('author_id')->constrained('authors')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('isbn')->nullable();
            $table->year('year')->nullable();
            $table->string('publisher')->nullable();
            $table->unsignedInteger('pages')->nullable();
            $table->string('language')->nullable();
            $table->string('cover')->nullable();
            $table->string('preview_file')->nullable();
            $table->string('full_file')->nullable();
            $table->text('description')->nullable();
            $table->text('table_of_contents')->nullable();
            $table->string('tags')->nullable();
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('downloads')->default(0);
            $table->boolean('featured')->default(false);
            $table->enum('status', ['draft', 'review', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('slug');
            $table->index('title');
            $table->index('isbn');
            $table->index('year');
            $table->index('status');
            $table->index('featured');
            $table->index('category_id');
            $table->index('author_id');
            $table->index('published_at');
            $table->index(['category_id', 'status']);
            $table->index(['author_id', 'status']);
            $table->index(['status', 'published_at']);
            $table->index(['featured', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
