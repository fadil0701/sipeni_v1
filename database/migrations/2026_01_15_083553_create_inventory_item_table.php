<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_item', function (Blueprint $table) {
            $table->id('id_item');
            $table->foreignId('id_inventory')->constrained('data_inventory', 'id_inventory')->onDelete('cascade');
            $table->string('kode_register', 100)->unique(); // Format: [UNIT]/[KODE_BARANG]/[TAHUN]/[URUT]
            $table->string('no_seri', 100)->nullable();
            $table->enum('kondisi_item', ['BAIK', 'RUSAK_RINGAN', 'RUSAK_BERAT'])->default('BAIK');
            $table->enum('status_item', ['AKTIF', 'DISTRIBUSI', 'NONAKTIF'])->default('AKTIF');
            $table->foreignId('id_gudang')->nullable()->constrained('master_gudang', 'id_gudang')->onDelete('set null');
            $table->foreignId('id_ruangan')->nullable()->constrained('master_ruangan', 'id_ruangan')->onDelete('set null');
            $table->text('qr_code')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_item');
    }
};
