<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('master_jabatan', function (Blueprint $table) {
            $table->integer('urutan')->default(0)->after('nama_jabatan');
            $table->foreignId('role_id')->nullable()->after('urutan')->constrained('roles')->onDelete('set null');
            $table->text('deskripsi')->nullable()->after('role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_jabatan', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['urutan', 'role_id', 'deskripsi']);
        });
    }
};
