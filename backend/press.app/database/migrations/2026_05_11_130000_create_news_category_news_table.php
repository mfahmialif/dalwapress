<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_category_news', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->constrained('news')->cascadeOnDelete();
            $table->foreignId('news_category_id')->constrained('news_categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['news_id', 'news_category_id']);
            $table->index('news_category_id');
        });

        DB::table('news')
            ->whereNotNull('news_category_id')
            ->select(['id', 'news_category_id'])
            ->orderBy('id')
            ->chunk(200, function ($items) {
                $now = now();
                $rows = $items->map(fn ($item) => [
                    'news_id' => $item->id,
                    'news_category_id' => $item->news_category_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                DB::table('news_category_news')->insertOrIgnore($rows);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_category_news');
    }
};
