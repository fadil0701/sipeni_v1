<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_permintaan', function (Blueprint $table) {
            $table->id('id_approval');
            $table->string('modul_approval', 50); // PERMINTAAN_BARANG, PEMELIHARAAN, DLL
            $table->unsignedBigInteger('id_referensi'); // ID dari modul yang di-approve
            $table->foreignId('id_approver')->constrained('users')->onDelete('cascade'); // Kepala
            $table->enum('status_approval', ['MENUNGGU', 'DISETUJUI', 'DITOLAK'])->default('MENUNGGU');
            $table->text('catatan')->nullable();
            $table->timestamp('tanggal_approval')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_permintaan');
    }
};
