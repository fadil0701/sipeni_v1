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
        Schema::create('permintaan_pemeliharaan', function (Blueprint $table) {
            $table->id('id_permintaan_pemeliharaan');
            $table->string('no_permintaan_pemeliharaan', 50)->unique();
            $table->foreignId('id_register_aset')->constrained('register_aset', 'id_register_aset')->onDelete('cascade');
            $table->foreignId('id_unit_kerja')->constrained('master_unit_kerja', 'id_unit_kerja')->onDelete('cascade');
            $table->foreignId('id_pemohon')->constrained('master_pegawai')->onDelete('cascade');
            $table->date('tanggal_permintaan');
            $table->enum('jenis_pemeliharaan', ['RUTIN', 'KALIBRASI', 'PERBAIKAN', 'PENGGANTIAN_SPAREPART'])->default('RUTIN');
            $table->enum('prioritas', ['RENDAH', 'SEDANG', 'TINGGI', 'DARURAT'])->default('SEDANG');
            $table->enum('status_permintaan', ['DRAFT', 'DIAJUKAN', 'DISETUJUI', 'DITOLAK', 'DIPROSES', 'SELESAI', 'DIBATALKAN'])->default('DRAFT');
            $table->text('deskripsi_kerusakan')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permintaan_pemeliharaan');
    }
};


