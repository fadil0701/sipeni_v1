<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_sub_kegiatan', function (Blueprint $table) {
            $table->id('id_sub_kegiatan');
            $table->foreignId('id_kegiatan')->constrained('master_kegiatan', 'id_kegiatan')->onDelete('cascade');
            $table->string('nama_sub_kegiatan', 255);
            $table->string('kode_sub_kegiatan', 50)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_sub_kegiatan');
    }
};
