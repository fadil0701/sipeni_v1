<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_flow_definition', function (Blueprint $table) {
            $table->id();
            $table->string('modul_approval', 50); // PERMINTAAN_BARANG, PEMELIHARAAN, DLL
            $table->integer('step_order'); // Urutan step (1, 2, 3, ...)
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('cascade');
            $table->string('nama_step', 100); // Nama step: "Mengetahui", "Verifikasi", "Approve", dll
            $table->enum('status', ['MENUNGGU', 'DIKETAHUI', 'DIVERIFIKASI', 'DISETUJUI', 'DITOLAK', 'DIDISPOSISIKAN'])->default('MENUNGGU');
            $table->text('status_text')->nullable(); // Deskripsi status
            $table->boolean('is_required')->default(true); // Apakah step ini wajib
            $table->boolean('can_reject')->default(false); // Apakah step ini bisa reject
            $table->boolean('can_approve')->default(false); // Apakah step ini bisa approve
            $table->timestamps();

            $table->unique(['modul_approval', 'step_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_flow_definition');
    }
};
