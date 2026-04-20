<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_program', function (Blueprint $table) {
            $table->string('kode_program', 100)->nullable()->unique()->after('id_program');
        });

        Schema::table('master_kegiatan', function (Blueprint $table) {
            $table->string('kode_kegiatan', 100)->nullable()->unique()->after('id_program');
        });
    }

    public function down(): void
    {
        Schema::table('master_program', function (Blueprint $table) {
            $table->dropUnique(['kode_program']);
            $table->dropColumn('kode_program');
        });

        Schema::table('master_kegiatan', function (Blueprint $table) {
            $table->dropUnique(['kode_kegiatan']);
            $table->dropColumn('kode_kegiatan');
        });
    }
};
