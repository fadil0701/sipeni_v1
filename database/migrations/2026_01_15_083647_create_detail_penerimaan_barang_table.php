<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_penerimaan_barang', function (Blueprint $table) {
            $table->id('id_detail_penerimaan');
            $table->foreignId('id_penerimaan')->constrained('penerimaan_barang', 'id_penerimaan')->onDelete('cascade');
            $table->foreignId('id_inventory')->constrained('data_inventory', 'id_inventory')->onDelete('cascade');
            $table->decimal('qty_diterima', 15, 2);
            $table->foreignId('id_satuan')->constrained('master_satuan', 'id_satuan')->onDelete('cascade');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_penerimaan_barang');
    }
};
