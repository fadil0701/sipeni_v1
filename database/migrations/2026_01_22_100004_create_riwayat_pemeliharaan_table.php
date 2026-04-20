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
        Schema::create('riwayat_pemeliharaan', function (Blueprint $table) {
            $table->id('id_riwayat');
            $table->foreignId('id_register_aset')->constrained('register_aset', 'id_register_aset')->onDelete('cascade');
            $table->foreignId('id_permintaan_pemeliharaan')->nullable()->constrained('permintaan_pemeliharaan', 'id_permintaan_pemeliharaan')->onDelete('set null');
            $table->foreignId('id_service_report')->nullable()->constrained('service_report', 'id_service_report')->onDelete('set null');
            $table->foreignId('id_kalibrasi')->nullable()->constrained('kalibrasi_aset', 'id_kalibrasi')->onDelete('set null');
            $table->date('tanggal_pemeliharaan');
            $table->enum('jenis_pemeliharaan', ['RUTIN', 'KALIBRASI', 'PERBAIKAN', 'PENGGANTIAN_SPAREPART'])->default('RUTIN');
            $table->enum('status', ['SELESAI', 'GAGAL', 'DIBATALKAN'])->default('SELESAI');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_pemeliharaan');
    }
};


