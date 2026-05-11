<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });

        // Tambah kolom role_id dan status ke users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('username')->constrained('roles')->nullOnDelete();
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->after('remember_token');
            $table->timestamp('last_active_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id', 'status', 'last_active_at']);
        });

        Schema::dropIfExists('roles');
    }
};
