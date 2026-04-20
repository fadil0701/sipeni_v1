<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kontrak', function (Blueprint $table) {
            $table->id('id_kontrak');
            $table->foreignId('id_paket')->constrained('pengadaan_paket', 'id_paket')->onDelete('cascade');
            $table->string('no_kontrak', 100)->unique();
            $table->string('no_sp', 100)->nullable()->unique();
            $table->string('no_po', 100)->nullable()->unique();
            $table->string('nama_vendor', 255);
            $table->string('npwp_vendor', 50)->nullable();
            $table->text('alamat_vendor')->nullable();
            $table->decimal('nilai_kontrak', 15, 2);
            $table->date('tanggal_kontrak');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('jenis_pembayaran', ['TUNAI', 'UANG_MUKA', 'TERMIN', 'PELUNASAN'])->default('TERMIN');
            $table->integer('jumlah_termin')->default(1);
            $table->enum('status_kontrak', ['DRAFT', 'AKTIF', 'SELESAI', 'DIBATALKAN'])->default('DRAFT');
            $table->string('upload_dokumen')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kontrak');
    }
};
