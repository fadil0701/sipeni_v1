<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_peminjaman_barang', function (Blueprint $table) {
            $table->id('id_detail_peminjaman');
            $table->unsignedBigInteger('id_peminjaman');
            $table->unsignedBigInteger('id_data_barang');
            $table->decimal('qty_pinjam', 12, 2);
            $table->unsignedBigInteger('id_satuan');
            $table->string('kondisi_serah', 100)->nullable();
            $table->string('kondisi_kembali', 100)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('id_peminjaman')->references('id_peminjaman')->on('peminjaman_barang')->cascadeOnDelete();
            $table->foreign('id_data_barang')->references('id_data_barang')->on('master_data_barang')->restrictOnDelete();
            $table->foreign('id_satuan')->references('id_satuan')->on('master_satuan')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_peminjaman_barang');
    }
};

