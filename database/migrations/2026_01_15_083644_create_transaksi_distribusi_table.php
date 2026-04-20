<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_distribusi', function (Blueprint $table) {
            $table->id('id_distribusi');
            $table->string('no_sbbk', 50)->unique();
            $table->foreignId('id_permintaan')->nullable()->constrained('permintaan_barang', 'id_permintaan')->onDelete('set null');
            $table->dateTime('tanggal_distribusi');
            $table->foreignId('id_gudang_asal')->constrained('master_gudang', 'id_gudang')->onDelete('cascade');
            $table->foreignId('id_gudang_tujuan')->constrained('master_gudang', 'id_gudang')->onDelete('cascade');
            $table->foreignId('id_pegawai_pengirim')->constrained('master_pegawai')->onDelete('cascade');
            $table->enum('status_distribusi', ['DRAFT', 'DIKIRIM', 'SELESAI'])->default('DRAFT');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_distribusi');
    }
};
