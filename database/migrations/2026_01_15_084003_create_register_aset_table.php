<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('register_aset', function (Blueprint $table) {
            $table->id('id_register_aset');
            $table->foreignId('id_inventory')->constrained('data_inventory', 'id_inventory')->onDelete('cascade');
            $table->foreignId('id_unit_kerja')->constrained('master_unit_kerja', 'id_unit_kerja')->onDelete('cascade');
            $table->string('nomor_register', 100)->unique();
            $table->enum('kondisi_aset', ['BAIK', 'RUSAK_RINGAN', 'RUSAK_BERAT'])->default('BAIK');
            $table->date('tanggal_perolehan');
            $table->enum('status_aset', ['AKTIF', 'NONAKTIF'])->default('AKTIF');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('register_aset');
    }
};
