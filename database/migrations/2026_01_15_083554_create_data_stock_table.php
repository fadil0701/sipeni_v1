<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_stock', function (Blueprint $table) {
            $table->id('id_stock');
            $table->foreignId('id_data_barang')->constrained('master_data_barang', 'id_data_barang')->onDelete('cascade');
            $table->foreignId('id_gudang')->constrained('master_gudang', 'id_gudang')->onDelete('cascade');
            $table->decimal('qty_awal', 15, 2)->default(0);
            $table->decimal('qty_masuk', 15, 2)->default(0);
            $table->decimal('qty_keluar', 15, 2)->default(0);
            $table->decimal('qty_akhir', 15, 2)->default(0);
            $table->foreignId('id_satuan')->constrained('master_satuan', 'id_satuan')->onDelete('cascade');
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();
            
            // Unique constraint untuk kombinasi barang dan gudang
            $table->unique(['id_data_barang', 'id_gudang']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_stock');
    }
};
