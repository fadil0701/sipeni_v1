<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_log', function (Blueprint $table) {
            $table->id();
            $table->string('modul_approval', 50); // PERMINTAAN_BARANG, PEMELIHARAAN, DLL
            $table->unsignedBigInteger('id_referensi'); // ID dari modul yang di-approve (id_permintaan, dll)
            $table->foreignId('id_approval_flow')->nullable()->constrained('approval_flow_definition')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->enum('status', ['MENUNGGU', 'DIKETAHUI', 'DIVERIFIKASI', 'DISETUJUI', 'DITOLAK', 'DIDISPOSISIKAN'])->default('MENUNGGU');
            $table->text('catatan')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['modul_approval', 'id_referensi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_log');
    }
};
