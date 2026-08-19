<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->foreignId('submission_id')->nullable()->after('author_id')
                ->constrained('submissions')->nullOnDelete();
            $table->index('submission_id');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['submission_id']);
            $table->dropIndex(['submission_id']);
            $table->dropColumn('submission_id');
        });
    }
};
