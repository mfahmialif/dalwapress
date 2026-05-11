<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('royalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('author_id')->constrained('authors')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->unsignedInteger('sold_qty')->default(0);
            $table->decimal('sale_price_per_unit', 15, 2)->default(0);
            $table->decimal('royalty_per_unit', 15, 2)->default(0);
            $table->decimal('gross_amount', 15, 2)->default(0);
            $table->decimal('royalty_amount', 15, 2)->default(0);
            $table->enum('status', ['draft', 'pending', 'paid'])->default('draft');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('book_id');
            $table->index('author_id');
            $table->index('status');
            $table->index(['period_year', 'period_month']);
            $table->index(['author_id', 'status']);
            $table->index(['book_id', 'period_year', 'period_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('royalties');
    }
};
