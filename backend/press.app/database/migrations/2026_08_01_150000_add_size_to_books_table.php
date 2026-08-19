<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('books', 'size')) {
            Schema::table('books', function (Blueprint $table) {
                $table->string('size')->nullable()->after('pages');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('books', 'size')) {
            Schema::table('books', function (Blueprint $table) {
                $table->dropColumn('size');
            });
        }
    }
};
