<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permintaan_barang', function (Blueprint $table) {
            $table->id('id_permintaan');
            $table->string('no_permintaan', 50)->unique();
            $table->foreignId('id_unit_kerja')->constrained('master_unit_kerja', 'id_unit_kerja')->onDelete('cascade');
            $table->foreignId('id_pemohon')->constrained('master_pegawai')->onDelete('cascade');
            $table->date('tanggal_permintaan');
            $table->enum('jenis_permintaan', ['BARANG', 'ASET']);
            $table->enum('status_permintaan', ['DRAFT', 'DIAJUKAN', 'DISETUJUI', 'DITOLAK'])->default('DRAFT');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_barang');
    }
};
