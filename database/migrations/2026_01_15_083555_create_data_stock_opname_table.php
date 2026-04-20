<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_stock_opname', function (Blueprint $table) {
            $table->id('id_opname');
            $table->foreignId('id_data_barang')->constrained('master_data_barang', 'id_data_barang')->onDelete('cascade');
            $table->foreignId('id_gudang')->constrained('master_gudang', 'id_gudang')->onDelete('cascade');
            $table->date('tanggal_opname');
            $table->decimal('qty_sistem', 15, 2);
            $table->decimal('qty_fisik', 15, 2);
            $table->decimal('selisih', 15, 2);
            $table->text('keterangan')->nullable();
            $table->foreignId('id_petugas')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_stock_opname');
    }
};
