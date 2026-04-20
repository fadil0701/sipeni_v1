<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengadaan_paket', function (Blueprint $table) {
            $table->id('id_paket');
            $table->foreignId('id_sub_kegiatan')->constrained('master_sub_kegiatan', 'id_sub_kegiatan')->onDelete('cascade');
            $table->foreignId('id_rku')->nullable()->constrained('rku_header', 'id_rku')->onDelete('set null');
            $table->string('no_paket', 100)->unique();
            $table->string('nama_paket', 255);
            $table->text('deskripsi_paket')->nullable();
            $table->enum('metode_pengadaan', ['PEMILIHAN_LANGSUNG', 'PENUNJUKAN_LANGSUNG', 'TENDER', 'SWAKELOLA'])->default('PEMILIHAN_LANGSUNG');
            $table->decimal('nilai_paket', 15, 2)->default(0);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->enum('status_paket', ['DRAFT', 'DIAJUKAN', 'DIPROSES', 'SELESAI', 'DIBATALKAN'])->default('DRAFT');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengadaan_paket');
    }
};
