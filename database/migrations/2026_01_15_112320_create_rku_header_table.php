<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rku_header', function (Blueprint $table) {
            $table->id('id_rku');
            $table->foreignId('id_unit_kerja')->constrained('master_unit_kerja', 'id_unit_kerja')->onDelete('cascade');
            $table->foreignId('id_sub_kegiatan')->constrained('master_sub_kegiatan', 'id_sub_kegiatan')->onDelete('cascade');
            $table->string('no_rku', 100)->unique();
            $table->string('tahun_anggaran', 4);
            $table->date('tanggal_pengajuan');
            $table->enum('jenis_rku', ['BARANG', 'ASET'])->default('BARANG');
            $table->enum('status_rku', ['DRAFT', 'DIAJUKAN', 'DISETUJUI', 'DITOLAK', 'DIPROSES'])->default('DRAFT');
            $table->foreignId('id_pengaju')->nullable()->constrained('master_pegawai')->onDelete('set null');
            $table->foreignId('id_approver')->nullable()->constrained('master_pegawai')->onDelete('set null');
            $table->date('tanggal_approval')->nullable();
            $table->text('catatan_approval')->nullable();
            $table->text('keterangan')->nullable();
            $table->decimal('total_anggaran', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rku_header');
    }
};
