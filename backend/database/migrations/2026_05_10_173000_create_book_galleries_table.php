<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->string('image')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('book_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_galleries');
    }
};
