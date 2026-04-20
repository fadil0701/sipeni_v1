<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rku_detail', function (Blueprint $table) {
            $table->id('id_rku_detail');
            $table->foreignId('id_rku')->constrained('rku_header', 'id_rku')->onDelete('cascade');
            $table->foreignId('id_data_barang')->constrained('master_data_barang', 'id_data_barang')->onDelete('cascade');
            $table->decimal('qty_rencana', 10, 2);
            $table->foreignId('id_satuan')->constrained('master_satuan', 'id_satuan')->onDelete('cascade');
            $table->decimal('harga_satuan_rencana', 15, 2)->default(0);
            $table->decimal('subtotal_rencana', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rku_detail');
    }
};
