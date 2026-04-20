<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_aset', function (Blueprint $table) {
            $table->id('id_aset');
            $table->string('nama_aset', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_aset');
    }
};
