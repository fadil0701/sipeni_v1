<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemeliharaan_aset', function (Blueprint $table) {
            $table->id('id_pemeliharaan');
            $table->foreignId('id_item')->constrained('inventory_item', 'id_item')->onDelete('cascade');
            $table->enum('jenis_pemeliharaan', ['RUTIN', 'KALIBRASI', 'PERBAIKAN']);
            $table->date('tanggal');
            $table->string('vendor', 255)->nullable();
            $table->decimal('biaya', 15, 2)->default(0);
            $table->string('laporan_service', 255)->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemeliharaan_aset');
    }
};
