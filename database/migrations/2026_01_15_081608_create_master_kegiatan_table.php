<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_kegiatan', function (Blueprint $table) {
            $table->id('id_kegiatan');
            $table->foreignId('id_program')->constrained('master_program', 'id_program')->onDelete('cascade');
            $table->string('nama_kegiatan', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_kegiatan');
    }
};
