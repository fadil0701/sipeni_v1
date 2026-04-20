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
        Schema::create('jadwal_maintenance', function (Blueprint $table) {
            $table->id('id_jadwal');
            $table->foreignId('id_register_aset')->constrained('register_aset', 'id_register_aset')->onDelete('cascade');
            $table->enum('jenis_maintenance', ['RUTIN', 'KALIBRASI', 'PERBAIKAN', 'PENGGANTIAN_SPAREPART'])->default('RUTIN');
            $table->enum('periode', ['HARIAN', 'MINGGUAN', 'BULANAN', '3_BULAN', '6_BULAN', 'TAHUNAN', 'CUSTOM'])->default('BULANAN');
            $table->integer('interval_hari')->nullable(); // Untuk periode CUSTOM
            $table->date('tanggal_mulai');
            $table->date('tanggal_selanjutnya')->nullable(); // Tanggal maintenance berikutnya
            $table->date('tanggal_terakhir')->nullable(); // Tanggal maintenance terakhir dilakukan
            $table->enum('status', ['AKTIF', 'NONAKTIF', 'SELESAI'])->default('AKTIF');
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
        Schema::dropIfExists('jadwal_maintenance');
    }
};


