<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_subjenis_barang', function (Blueprint $table) {
            $table->id('id_subjenis_barang');
            $table->foreignId('id_jenis_barang')->constrained('master_jenis_barang', 'id_jenis_barang')->onDelete('cascade');
            $table->string('kode_subjenis_barang', 50);
            $table->string('nama_subjenis_barang', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_subjenis_barang');
    }
};
