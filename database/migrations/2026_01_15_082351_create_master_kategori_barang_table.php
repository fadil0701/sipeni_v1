<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_kategori_barang', function (Blueprint $table) {
            $table->id('id_kategori_barang');
            $table->foreignId('id_kode_barang')->constrained('master_kode_barang', 'id_kode_barang')->onDelete('cascade');
            $table->string('kode_kategori_barang', 50);
            $table->string('nama_kategori_barang', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_kategori_barang');
    }
};
