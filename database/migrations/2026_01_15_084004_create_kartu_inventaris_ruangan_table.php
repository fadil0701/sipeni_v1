<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kartu_inventaris_ruangan', function (Blueprint $table) {
            $table->id('id_kir');
            $table->foreignId('id_register_aset')->constrained('register_aset', 'id_register_aset')->onDelete('cascade');
            $table->foreignId('id_ruangan')->constrained('master_ruangan', 'id_ruangan')->onDelete('cascade');
            $table->foreignId('id_penanggung_jawab')->constrained('master_pegawai')->onDelete('cascade');
            $table->date('tanggal_penempatan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kartu_inventaris_ruangan');
    }
};
