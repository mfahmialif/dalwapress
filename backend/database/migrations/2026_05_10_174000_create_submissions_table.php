<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('book_categories')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->nullable()->index();
            $table->string('author_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('manuscript_file')->nullable();
            $table->string('cover_file')->nullable();
            $table->text('description')->nullable();
            $table->text('note')->nullable();
            $table->enum('status', ['submitted', 'under_review', 'revision', 'accepted', 'rejected', 'published'])->default('submitted');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('title');
            $table->index('author_name');
            $table->index('email');
            $table->index('category_id');
            $table->index('status');
            $table->index('submitted_at');
            $table->index('reviewed_at');
            $table->index(['category_id', 'status']);
            $table->index(['status', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
