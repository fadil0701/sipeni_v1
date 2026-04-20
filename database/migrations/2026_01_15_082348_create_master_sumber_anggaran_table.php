<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_sumber_anggaran', function (Blueprint $table) {
            $table->id('id_anggaran');
            $table->string('nama_anggaran', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_sumber_anggaran');
    }
};
