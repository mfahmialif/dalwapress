<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add current_round to submissions
        if (!Schema::hasColumn('submissions', 'current_round')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->integer('current_round')->default(1)->after('status');
            });
        }

        // 2. Manuscript Versions Table
        if (!Schema::hasTable('submission_manuscript_versions')) {
            Schema::create('submission_manuscript_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->integer('version_number')->default(1);
                $table->integer('round')->default(1);
                $table->string('file_path');
                $table->text('note')->nullable();
                $table->timestamps();

                $table->index(['submission_id', 'version_number'], 'sub_ms_ver_idx');
                $table->index(['submission_id', 'round'], 'sub_ms_round_idx');
            });
        }

        // 3. Editor Evaluations Table
        if (!Schema::hasTable('submission_editor_evaluations')) {
            Schema::create('submission_editor_evaluations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
                $table->foreignId('editor_id')->constrained('users')->cascadeOnDelete();
                $table->integer('round')->default(1);
                $table->enum('recommendation', ['accept', 'revision', 'reject']);
                $table->text('notes')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();

                $table->unique(['submission_id', 'round', 'editor_id'], 'sub_ed_eval_uniq');
                $table->index(['submission_id', 'round'], 'sub_eval_round_idx');
            });
        }

        // 4. Editorial Decisions Table
        if (!Schema::hasTable('submission_editorial_decisions')) {
            Schema::create('submission_editorial_decisions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
                $table->integer('round')->default(1);
                $table->enum('decision', ['revision_required', 'accepted', 'rejected']);
                $table->text('notes')->nullable();
                $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('decided_at')->nullable();
                $table->timestamps();

                $table->unique(['submission_id', 'round'], 'sub_ed_dec_uniq');
                $table->index('submission_id', 'sub_dec_sub_idx');
            });
        }

        // Backfill manuscript_file from existing submissions into manuscript_versions v1
        $submissions = DB::table('submissions')->whereNotNull('manuscript_file')->get();
        foreach ($submissions as $sub) {
            $exists = DB::table('submission_manuscript_versions')
                ->where('submission_id', $sub->id)
                ->where('version_number', 1)
                ->exists();

            if (!$exists) {
                DB::table('submission_manuscript_versions')->insert([
                    'submission_id' => $sub->id,
                    'user_id' => $sub->user_id ?? null,
                    'version_number' => 1,
                    'round' => 1,
                    'file_path' => $sub->manuscript_file,
                    'note' => 'Initial manuscript submission',
                    'created_at' => $sub->submitted_at ?? now(),
                    'updated_at' => $sub->submitted_at ?? now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_editorial_decisions');
        Schema::dropIfExists('submission_editor_evaluations');
        Schema::dropIfExists('submission_manuscript_versions');

        if (Schema::hasColumn('submissions', 'current_round')) {
            Schema::table('submissions', function (Blueprint $table) {
                $table->dropColumn('current_round');
            });
        }
    }
};
