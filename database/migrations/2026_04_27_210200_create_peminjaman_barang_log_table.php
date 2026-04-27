<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman_barang_log', function (Blueprint $table) {
            $table->id('id_log');
            $table->unsignedBigInteger('id_peminjaman');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('aksi', 60);
            $table->string('status_sebelum', 50)->nullable();
            $table->string('status_sesudah', 50)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('id_peminjaman')->references('id_peminjaman')->on('peminjaman_barang')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman_barang_log');
    }
};

