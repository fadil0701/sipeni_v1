<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('draft_detail_distribusi', function (Blueprint $table) {
            $table->id('id_draft_detail');
            $table->foreignId('id_permintaan')->constrained('permintaan_barang', 'id_permintaan')->onDelete('cascade');
            $table->foreignId('id_inventory')->constrained('data_inventory', 'id_inventory')->onDelete('cascade');
            $table->foreignId('id_gudang_asal')->constrained('master_gudang', 'id_gudang')->onDelete('cascade');
            $table->decimal('qty_distribusi', 15, 2);
            $table->foreignId('id_satuan')->constrained('master_satuan', 'id_satuan')->onDelete('cascade');
            $table->decimal('harga_satuan', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->string('kategori_gudang', 20); // ASET, PERSEDIAAN, FARMASI
            $table->foreignId('created_by')->constrained('users', 'id')->onDelete('cascade'); // Admin gudang kategori yang membuat
            $table->enum('status', ['DRAFT', 'READY', 'COMPILED'])->default('DRAFT');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            // Index untuk query efisien (nama pendek untuk menghindari error MySQL)
            $table->index(['id_permintaan', 'kategori_gudang', 'status'], 'idx_draft_detail_permintaan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draft_detail_distribusi');
    }
};
