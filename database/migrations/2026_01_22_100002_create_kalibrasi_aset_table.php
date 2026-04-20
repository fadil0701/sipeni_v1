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
        Schema::create('kalibrasi_aset', function (Blueprint $table) {
            $table->id('id_kalibrasi');
            $table->string('no_kalibrasi', 50)->unique();
            $table->foreignId('id_register_aset')->constrained('register_aset', 'id_register_aset')->onDelete('cascade');
            $table->foreignId('id_permintaan_pemeliharaan')->nullable()->constrained('permintaan_pemeliharaan', 'id_permintaan_pemeliharaan')->onDelete('set null');
            $table->date('tanggal_kalibrasi');
            $table->date('tanggal_berlaku');
            $table->date('tanggal_kadaluarsa');
            $table->string('lembaga_kalibrasi', 255)->nullable();
            $table->string('no_sertifikat', 100)->nullable();
            $table->enum('status_kalibrasi', ['VALID', 'KADALUARSA', 'MENUNGGU', 'DITOLAK'])->default('VALID');
            $table->decimal('biaya_kalibrasi', 15, 2)->default(0);
            $table->string('file_sertifikat', 255)->nullable();
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
        Schema::dropIfExists('kalibrasi_aset');
    }
};


