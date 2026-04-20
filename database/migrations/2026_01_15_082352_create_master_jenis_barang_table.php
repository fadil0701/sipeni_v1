<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_jenis_barang', function (Blueprint $table) {
            $table->id('id_jenis_barang');
            $table->foreignId('id_kategori_barang')->constrained('master_kategori_barang', 'id_kategori_barang')->onDelete('cascade');
            $table->string('kode_jenis_barang', 50);
            $table->string('nama_jenis_barang', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_jenis_barang');
    }
};
