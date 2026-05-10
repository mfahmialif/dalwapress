<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_editor_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->foreignId('editor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('role', ['primary', 'co_editor'])->default('co_editor');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['submission_id', 'editor_id']);
            $table->index(['submission_id', 'role']);
            $table->index(['editor_id', 'role']);
        });

        Schema::table('submission_reviews', function (Blueprint $table) {
            $table->foreignId('editor_id')->nullable()->after('submission_id')->constrained('users')->nullOnDelete();
            $table->index('editor_id');
        });
    }

    public function down(): void
    {
        Schema::table('submission_reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('editor_id');
        });

        Schema::dropIfExists('submission_editor_assignments');
    }
};
