<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_pegawai', function (Blueprint $table) {
            $table->id();
            $table->string('nip_pegawai', 50)->unique();
            $table->string('nama_pegawai', 255);
            $table->foreignId('id_unit_kerja')->constrained('master_unit_kerja', 'id_unit_kerja')->onDelete('cascade');
            $table->foreignId('id_jabatan')->constrained('master_jabatan', 'id_jabatan')->onDelete('cascade');
            $table->string('email_pegawai', 255)->unique();
            $table->string('no_telp', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_pegawai');
    }
};
