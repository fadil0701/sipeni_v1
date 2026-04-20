<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_data_barang', function (Blueprint $table) {
            $table->id('id_data_barang');
            $table->foreignId('id_subjenis_barang')->constrained('master_subjenis_barang', 'id_subjenis_barang')->onDelete('cascade');
            $table->foreignId('id_satuan')->constrained('master_satuan', 'id_satuan')->onDelete('cascade');
            $table->string('kode_data_barang', 50)->unique();
            $table->string('nama_barang', 255);
            $table->text('deskripsi')->nullable();
            $table->string('upload_foto', 255)->nullable();
            $table->string('foto_barang', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_data_barang');
    }
};
