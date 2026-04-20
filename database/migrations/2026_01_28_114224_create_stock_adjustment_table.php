<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustment', function (Blueprint $table) {
            $table->id('id_adjustment');
            $table->foreignId('id_stock')->constrained('data_stock', 'id_stock')->onDelete('cascade');
            $table->foreignId('id_data_barang')->constrained('master_data_barang', 'id_data_barang')->onDelete('cascade');
            $table->foreignId('id_gudang')->constrained('master_gudang', 'id_gudang')->onDelete('cascade');
            $table->date('tanggal_adjustment');
            $table->decimal('qty_sebelum', 15, 2);
            $table->decimal('qty_sesudah', 15, 2);
            $table->decimal('qty_selisih', 15, 2); // positif = tambah, negatif = kurang
            $table->enum('jenis_adjustment', ['PENAMBAHAN', 'PENGURANGAN', 'KOREKSI', 'OPNAME'])->default('KOREKSI');
            $table->text('alasan')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('id_petugas')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['DRAFT', 'DIAJUKAN', 'DISETUJUI', 'DITOLAK'])->default('DRAFT');
            $table->foreignId('id_approver')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('tanggal_approval')->nullable();
            $table->text('catatan_approval')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment');
    }
};
