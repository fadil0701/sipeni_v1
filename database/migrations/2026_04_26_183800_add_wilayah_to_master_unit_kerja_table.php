<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_unit_kerja', function (Blueprint $table) {
            $table->string('kota_kabupaten', 100)->nullable()->after('nama_unit_kerja');
            $table->string('kecamatan', 100)->nullable()->after('kota_kabupaten');
        });
    }

    public function down(): void
    {
        Schema::table('master_unit_kerja', function (Blueprint $table) {
            $table->dropColumn(['kota_kabupaten', 'kecamatan']);
        });
    }
};
