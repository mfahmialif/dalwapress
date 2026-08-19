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
        Schema::table('submissions', function (Blueprint $table) {
            $table->string('editor')->nullable()->after('note');
            $table->string('layouter')->nullable()->after('editor');
            $table->string('desain_sampul')->nullable()->after('layouter');
            $table->string('halaman')->nullable()->after('desain_sampul');
            $table->string('ukuran')->nullable()->after('halaman');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['editor', 'layouter', 'desain_sampul', 'halaman', 'ukuran']);
        });
    }
};
