<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_gudang', function (Blueprint $table) {
            $table->id('id_gudang');
            $table->foreignId('id_unit_kerja')->constrained('master_unit_kerja', 'id_unit_kerja')->onDelete('cascade');
            $table->string('nama_gudang', 255);
            $table->enum('jenis_gudang', ['PUSAT', 'UNIT']);
            $table->enum('kategori_gudang', ['ASET', 'PERSEDIAAN', 'FARMASI'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_gudang');
    }
};
