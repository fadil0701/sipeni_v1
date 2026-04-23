<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('master_data_barang_permendagri', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_data_barang')->unique();

            $table->string('kode_barang_108', 100)->nullable();
            $table->string('kode_akun', 10)->nullable();
            $table->string('kode_kelompok', 10)->nullable();
            $table->string('kode_jenis_108', 10)->nullable();
            $table->string('kode_objek', 10)->nullable();
            $table->string('kode_rincian_objek', 10)->nullable();
            $table->string('kode_sub_rincian_objek', 10)->nullable();
            $table->string('kode_sub_sub_rincian_objek', 10)->nullable();

            $table->string('sumber_mapping', 20)->default('AUTO');
            $table->string('status_validasi', 20)->default('DRAFT');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('id_data_barang')
                ->references('id_data_barang')
                ->on('master_data_barang')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_data_barang_permendagri');
    }
};

