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
        Schema::create('service_report', function (Blueprint $table) {
            $table->id('id_service_report');
            $table->string('no_service_report', 50)->unique();
            $table->foreignId('id_permintaan_pemeliharaan')->constrained('permintaan_pemeliharaan', 'id_permintaan_pemeliharaan')->onDelete('cascade');
            $table->foreignId('id_register_aset')->constrained('register_aset', 'id_register_aset')->onDelete('cascade');
            $table->date('tanggal_service');
            $table->date('tanggal_selesai')->nullable();
            $table->enum('jenis_service', ['RUTIN', 'KALIBRASI', 'PERBAIKAN', 'PENGGANTIAN_SPAREPART'])->default('RUTIN');
            $table->enum('status_service', ['MENUNGGU', 'DIPROSES', 'SELESAI', 'DITOLAK', 'DIBATALKAN'])->default('MENUNGGU');
            $table->string('vendor', 255)->nullable();
            $table->string('teknisi', 255)->nullable();
            $table->text('deskripsi_kerja')->nullable();
            $table->text('tindakan_yang_dilakukan')->nullable();
            $table->text('sparepart_yang_diganti')->nullable();
            $table->decimal('biaya_service', 15, 2)->default(0);
            $table->decimal('biaya_sparepart', 15, 2)->default(0);
            $table->decimal('total_biaya', 15, 2)->default(0);
            $table->enum('kondisi_setelah_service', ['BAIK', 'RUSAK_RINGAN', 'RUSAK_BERAT', 'TIDAK_BISA_DIPERBAIKI'])->nullable();
            $table->string('file_laporan', 255)->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_report');
    }
};


