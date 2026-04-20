<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_kode_barang', function (Blueprint $table) {
            $table->id('id_kode_barang');
            $table->foreignId('id_aset')->constrained('master_aset', 'id_aset')->onDelete('cascade');
            $table->string('kode_barang', 50)->unique();
            $table->string('nama_kode_barang', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_kode_barang');
    }
};
