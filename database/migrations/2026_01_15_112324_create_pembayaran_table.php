<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id('id_pembayaran');
            $table->foreignId('id_kontrak')->constrained('kontrak', 'id_kontrak')->onDelete('cascade');
            $table->string('no_pembayaran', 100)->unique();
            $table->enum('jenis_pembayaran', ['UANG_MUKA', 'TERMIN', 'PELUNASAN'])->default('TERMIN');
            $table->integer('termin_ke')->nullable(); // Untuk termin, misal: 1, 2, 3
            $table->decimal('nilai_pembayaran', 15, 2);
            $table->decimal('ppn', 15, 2)->default(0);
            $table->decimal('pph', 15, 2)->default(0);
            $table->decimal('total_pembayaran', 15, 2);
            $table->date('tanggal_pembayaran');
            $table->enum('status_pembayaran', ['DRAFT', 'DIAJUKAN', 'DIVERIFIKASI', 'DIBAYAR', 'DITOLAK'])->default('DRAFT');
            $table->foreignId('id_verifikator')->nullable()->constrained('master_pegawai')->onDelete('set null');
            $table->date('tanggal_verifikasi')->nullable();
            $table->text('catatan_verifikasi')->nullable();
            $table->string('no_bukti_bayar', 100)->nullable();
            $table->string('upload_bukti_bayar')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
