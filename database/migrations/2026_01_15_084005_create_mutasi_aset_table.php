<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutasi_aset', function (Blueprint $table) {
            $table->id('id_mutasi');
            $table->foreignId('id_register_aset')->constrained('register_aset', 'id_register_aset')->onDelete('cascade');
            $table->foreignId('id_ruangan_asal')->constrained('master_ruangan', 'id_ruangan')->onDelete('cascade');
            $table->foreignId('id_ruangan_tujuan')->constrained('master_ruangan', 'id_ruangan')->onDelete('cascade');
            $table->date('tanggal_mutasi');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi_aset');
    }
};
