<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penerimaan_barang', function (Blueprint $table) {
            $table->id('id_penerimaan');
            $table->string('no_penerimaan', 50)->unique();
            $table->foreignId('id_distribusi')->constrained('transaksi_distribusi', 'id_distribusi')->onDelete('cascade');
            $table->foreignId('id_unit_kerja')->constrained('master_unit_kerja', 'id_unit_kerja')->onDelete('cascade');
            $table->foreignId('id_pegawai_penerima')->constrained('master_pegawai')->onDelete('cascade');
            $table->date('tanggal_penerimaan');
            $table->enum('status_penerimaan', ['DITERIMA', 'DITOLAK'])->default('DITERIMA');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penerimaan_barang');
    }
};
