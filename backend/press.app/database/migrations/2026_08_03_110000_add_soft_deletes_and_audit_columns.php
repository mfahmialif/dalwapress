<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tablesFullNew = [
            'users', 'roles', 'authors', 'books',
            'book_categories', 'book_galleries', 'news_categories',
            'news_comments', 'comment_likes', 'submissions',
            'submission_reviews', 'submission_revisions',
            'submission_editor_assignments', 'submission_layouter_assignments',
            'submission_cover_assignments', 'submission_manuscript_versions',
            'submission_editor_evaluations', 'submission_editorial_decisions',
            'news_category_news', 'app_settings',
        ];

        foreach ($tablesFullNew as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->softDeletes();
                $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $t->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }

        Schema::table('news', function (Blueprint $t) {
            $t->softDeletes();
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('royalties', function (Blueprint $t) {
            $t->softDeletes();
            $t->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        $allTables = [
            'users', 'roles', 'authors', 'books',
            'book_categories', 'book_galleries', 'news_categories',
            'news_comments', 'comment_likes', 'submissions',
            'submission_reviews', 'submission_revisions',
            'submission_editor_assignments', 'submission_layouter_assignments',
            'submission_cover_assignments', 'submission_manuscript_versions',
            'submission_editor_evaluations', 'submission_editorial_decisions',
            'news_category_news', 'app_settings',
        ];

        foreach ($allTables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['created_by']);
                $t->dropForeign(['updated_by']);
                $t->dropForeign(['deleted_by']);
                $t->dropColumn(['deleted_at', 'created_by', 'updated_by', 'deleted_by']);
            });
        }

        Schema::table('news', function (Blueprint $t) {
            $t->dropForeign(['updated_by']);
            $t->dropForeign(['deleted_by']);
            $t->dropColumn(['deleted_at', 'updated_by', 'deleted_by']);
        });

        Schema::table('royalties', function (Blueprint $t) {
            $t->dropForeign(['deleted_by']);
            $t->dropColumn(['deleted_at', 'deleted_by']);
        });
    }
};
