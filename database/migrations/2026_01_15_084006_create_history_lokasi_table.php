<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('history_lokasi', function (Blueprint $table) {
            $table->id('id_history');
            $table->foreignId('id_inventory')->constrained('data_inventory', 'id_inventory')->onDelete('cascade');
            $table->foreignId('id_gudang_asal')->nullable()->constrained('master_gudang', 'id_gudang')->onDelete('set null');
            $table->foreignId('id_gudang_tujuan')->nullable()->constrained('master_gudang', 'id_gudang')->onDelete('set null');
            $table->unsignedBigInteger('id_transaksi')->nullable(); // ID dari transaksi terkait
            $table->enum('jenis_transaksi', ['DISTRIBUSI', 'PENERIMAAN', 'MUTASI']);
            $table->dateTime('tanggal_transaksi');
            $table->decimal('qty', 15, 2);
            $table->foreignId('id_satuan')->constrained('master_satuan', 'id_satuan')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('history_lokasi');
    }
};
