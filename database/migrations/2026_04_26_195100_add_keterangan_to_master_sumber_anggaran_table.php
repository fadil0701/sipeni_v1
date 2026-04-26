<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('master_sumber_anggaran', 'keterangan')) {
            Schema::table('master_sumber_anggaran', function (Blueprint $table) {
                $table->string('keterangan', 255)->nullable()->after('nama_anggaran');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('master_sumber_anggaran', 'keterangan')) {
            Schema::table('master_sumber_anggaran', function (Blueprint $table) {
                $table->dropColumn('keterangan');
            });
        }
    }
};
