<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('submission_authors')) {
            Schema::create('submission_authors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete()->cascadeOnUpdate();
                $table->foreignId('author_id')->nullable()->constrained('authors')->nullOnDelete()->cascadeOnUpdate();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('institution')->nullable();
                $table->string('role')->nullable()->default('Penulis Utama');
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

                $table->index(['submission_id', 'sort_order']);
                $table->index('name');
            });

            // Migrate existing submissions author information
            $existingSubmissions = DB::table('submissions')->get();
            foreach ($existingSubmissions as $sub) {
                $authorId = null;
                if ($sub->user_id) {
                    $authorId = DB::table('authors')->where('user_id', $sub->user_id)->value('id');
                }

                DB::table('submission_authors')->insert([
                    'submission_id' => $sub->id,
                    'author_id' => $authorId,
                    'name' => $sub->author_name ?: 'Penulis',
                    'email' => $sub->email,
                    'phone' => $sub->phone,
                    'institution' => null,
                    'role' => 'Penulis Utama',
                    'sort_order' => 0,
                    'created_at' => $sub->created_at ?? now(),
                    'updated_at' => $sub->updated_at ?? now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_authors');
    }
};
