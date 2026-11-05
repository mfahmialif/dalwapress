<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('book_authors')) {
            Schema::create('book_authors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('book_id')->constrained('books')->cascadeOnDelete()->cascadeOnUpdate();
                $table->foreignId('author_id')->constrained('authors')->cascadeOnDelete()->cascadeOnUpdate();
                $table->string('role')->nullable()->default('Penulis Utama');
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

                $table->unique(['book_id', 'author_id']);
                $table->index(['book_id', 'sort_order']);
            });

            // Migrate existing book author_id relations
            $existingBooks = DB::table('books')->whereNotNull('author_id')->get();
            foreach ($existingBooks as $book) {
                DB::table('book_authors')->insert([
                    'book_id' => $book->id,
                    'author_id' => $book->author_id,
                    'role' => 'Penulis Utama',
                    'sort_order' => 0,
                    'created_at' => $book->created_at ?? now(),
                    'updated_at' => $book->updated_at ?? now(),
                ]);
            }
        }

        // Make author_id in books nullable if not already
        Schema::table('books', function (Blueprint $table) {
            $table->foreignId('author_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_authors');
    }
};
