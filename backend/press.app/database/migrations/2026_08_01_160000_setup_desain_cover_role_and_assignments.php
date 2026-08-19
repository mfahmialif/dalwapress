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
        // 1. Insert or get Desain Cover Role
        DB::table('roles')->updateOrInsert(
            ['name' => 'Desain Cover'],
            [
                'description' => 'Akses dashboard desainer sampul untuk perancangan cover naskah.',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $coverRole = DB::table('roles')->where('name', 'Desain Cover')->first();

        if ($coverRole) {
            // 2. Create sample Desain Cover user
            DB::table('users')->updateOrInsert(
                ['username' => 'desain_cover'],
                [
                    'name' => 'Desainer Sampul Utama',
                    'email' => 'cover@uiidalwapress.com',
                    'password' => Hash::make('password'),
                    'role_id' => $coverRole->id,
                    'status' => 'Active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 3. Create submission_cover_assignments table
        if (!Schema::hasTable('submission_cover_assignments')) {
            Schema::create('submission_cover_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
                $table->foreignId('cover_designer_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('note')->nullable();
                $table->timestamps();

                $table->unique(['submission_id', 'cover_designer_id'], 'sub_cover_assign_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_cover_assignments');
    }
};
