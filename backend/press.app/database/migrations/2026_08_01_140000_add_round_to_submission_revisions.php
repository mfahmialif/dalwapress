<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('submission_revisions', 'round')) {
            Schema::table('submission_revisions', function (Blueprint $table) {
                $table->integer('round')->default(1)->after('user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('submission_revisions', 'round')) {
            Schema::table('submission_revisions', function (Blueprint $table) {
                $table->dropColumn('round');
            });
        }
    }
};
