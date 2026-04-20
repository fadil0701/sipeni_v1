<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_unit_kerja', function (Blueprint $table) {
            $table->id('id_unit_kerja');
            $table->string('kode_unit_kerja', 50)->unique();
            $table->string('nama_unit_kerja', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_unit_kerja');
    }
};
