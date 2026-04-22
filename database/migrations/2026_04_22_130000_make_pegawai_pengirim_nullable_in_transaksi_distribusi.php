<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_distribusi', function (Blueprint $table) {
            $table->unsignedBigInteger('id_pegawai_pengirim')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_distribusi', function (Blueprint $table) {
            $table->unsignedBigInteger('id_pegawai_pengirim')->nullable(false)->change();
        });
    }
};

