<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman_barang', function (Blueprint $table) {
            $table->id('id_peminjaman');
            $table->string('no_peminjaman', 50)->unique();
            $table->unsignedBigInteger('id_unit_peminjam');
            $table->unsignedBigInteger('id_pemohon');
            $table->enum('tujuan_peminjaman', ['UNIT', 'GUDANG_PUSAT']);
            $table->unsignedBigInteger('id_unit_pemilik')->nullable();
            $table->unsignedBigInteger('id_gudang_pusat')->nullable();
            $table->date('tanggal_pinjam');
            $table->date('tanggal_rencana_kembali');
            $table->dateTime('tanggal_serah_terima')->nullable();
            $table->dateTime('tanggal_pengembalian')->nullable();
            $table->string('status', 50)->default('DIAJUKAN');
            $table->text('alasan')->nullable();
            $table->timestamps();

            $table->foreign('id_unit_peminjam')->references('id_unit_kerja')->on('master_unit_kerja')->cascadeOnDelete();
            $table->foreign('id_unit_pemilik')->references('id_unit_kerja')->on('master_unit_kerja')->nullOnDelete();
            $table->foreign('id_gudang_pusat')->references('id_gudang')->on('master_gudang')->nullOnDelete();
            $table->foreign('id_pemohon')->references('id')->on('master_pegawai')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman_barang');
    }
};

