<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detail_retur_barang', function (Blueprint $table) {
            $table->id('id_detail_retur');
            $table->foreignId('id_retur')->constrained('retur_barang', 'id_retur')->onDelete('cascade');
            $table->foreignId('id_inventory')->constrained('data_inventory', 'id_inventory')->onDelete('cascade');
            $table->decimal('qty_retur', 15, 2);
            $table->foreignId('id_satuan')->constrained('master_satuan', 'id_satuan')->onDelete('cascade');
            $table->text('alasan_retur_item')->nullable(); // Alasan retur untuk item spesifik
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_retur_barang');
    }
};
