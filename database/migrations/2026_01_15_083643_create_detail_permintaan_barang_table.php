<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_permintaan_barang', function (Blueprint $table) {
            $table->id('id_detail_permintaan');
            $table->foreignId('id_permintaan')->constrained('permintaan_barang', 'id_permintaan')->onDelete('cascade');
            $table->foreignId('id_data_barang')->constrained('master_data_barang', 'id_data_barang')->onDelete('cascade');
            $table->decimal('qty_diminta', 15, 2);
            $table->foreignId('id_satuan')->constrained('master_satuan', 'id_satuan')->onDelete('cascade');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_permintaan_barang');
    }
};
