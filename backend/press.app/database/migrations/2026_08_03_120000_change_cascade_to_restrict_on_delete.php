<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $changes = [
            ['book_galleries', 'book_id', 'books'],
            ['submission_reviews', 'submission_id', 'submissions'],
            ['submission_revisions', 'submission_id', 'submissions'],
            ['submission_editor_assignments', 'submission_id', 'submissions'],
            ['submission_editor_assignments', 'editor_id', 'users'],
            ['submission_layouter_assignments', 'submission_id', 'submissions'],
            ['submission_layouter_assignments', 'layouter_id', 'users'],
            ['submission_cover_assignments', 'submission_id', 'submissions'],
            ['submission_cover_assignments', 'cover_designer_id', 'users'],
            ['submission_manuscript_versions', 'submission_id', 'submissions'],
            ['submission_editor_evaluations', 'submission_id', 'submissions'],
            ['submission_editor_evaluations', 'editor_id', 'users'],
            ['submission_editorial_decisions', 'submission_id', 'submissions'],
            ['news_category_news', 'news_id', 'news'],
            ['news_category_news', 'news_category_id', 'news_categories'],
            ['news_comments', 'news_id', 'news'],
            ['news_comments', 'parent_id', 'news_comments'],
            ['comment_likes', 'user_id', 'users'],
            ['comment_likes', 'news_comment_id', 'news_comments'],
        ];

        foreach ($changes as [$table, $column, $referenced]) {
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->dropForeign([$column]);
            });
            Schema::table($table, function (Blueprint $t) use ($column, $referenced) {
                $t->foreign($column)->references('id')->on($referenced)
                    ->restrictOnDelete()->cascadeOnUpdate();
            });
        }
    }

    public function down(): void
    {
        $changes = [
            ['book_galleries', 'book_id', 'books'],
            ['submission_reviews', 'submission_id', 'submissions'],
            ['submission_revisions', 'submission_id', 'submissions'],
            ['submission_editor_assignments', 'submission_id', 'submissions'],
            ['submission_editor_assignments', 'editor_id', 'users'],
            ['submission_layouter_assignments', 'submission_id', 'submissions'],
            ['submission_layouter_assignments', 'layouter_id', 'users'],
            ['submission_cover_assignments', 'submission_id', 'submissions'],
            ['submission_cover_assignments', 'cover_designer_id', 'users'],
            ['submission_manuscript_versions', 'submission_id', 'submissions'],
            ['submission_editor_evaluations', 'submission_id', 'submissions'],
            ['submission_editor_evaluations', 'editor_id', 'users'],
            ['submission_editorial_decisions', 'submission_id', 'submissions'],
            ['news_category_news', 'news_id', 'news'],
            ['news_category_news', 'news_category_id', 'news_categories'],
            ['news_comments', 'news_id', 'news'],
            ['news_comments', 'parent_id', 'news_comments'],
            ['comment_likes', 'user_id', 'users'],
            ['comment_likes', 'news_comment_id', 'news_comments'],
        ];

        foreach ($changes as [$table, $column, $referenced]) {
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->dropForeign([$column]);
            });
            Schema::table($table, function (Blueprint $t) use ($column, $referenced) {
                $t->foreign($column)->references('id')->on($referenced)->cascadeOnDelete();
            });
        }
    }
};
