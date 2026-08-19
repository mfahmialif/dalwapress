<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop temporary varchar columns from submissions table if present
        Schema::table('submissions', function (Blueprint $table) {
            $columnsToDrop = array_filter(
                ['editor', 'layouter', 'desain_sampul', 'halaman', 'ukuran'],
                fn ($col) => Schema::hasColumn('submissions', $col)
            );
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        // 2. Insert or get Layouter Role
        DB::table('roles')->updateOrInsert(
            ['name' => 'Layouter'],
            [
                'description' => 'Akses dashboard layouter untuk penataan letak naskah.',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $layouterRole = DB::table('roles')->where('name', 'Layouter')->first();

        if ($layouterRole) {
            // 3. Create sample Layouter user
            DB::table('users')->updateOrInsert(
                ['username' => 'layouter'],
                [
                    'name' => 'Layouter Utama',
                    'email' => 'layouter@uiidalwapress.com',
                    'password' => Hash::make('password'),
                    'role_id' => $layouterRole->id,
                    'status' => 'Active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 4. Create submission_layouter_assignments table
        if (!Schema::hasTable('submission_layouter_assignments')) {
            Schema::create('submission_layouter_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
                $table->foreignId('layouter_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('note')->nullable();
                $table->timestamps();

                $table->unique(['submission_id', 'layouter_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_layouter_assignments');
    }
};
