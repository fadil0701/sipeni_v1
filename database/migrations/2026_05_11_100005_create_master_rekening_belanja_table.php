<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_rekening_belanja', function (Blueprint $table) {
            $table->id('id');
            $table->string('kode_rekening', 50)->unique();
            $table->string('nama_rekening', 255);
            $table->string('jenis', 50)->nullable()->comment('Pendapatan/Belanja/Pembiayaan');
            $table->string('kelompok', 50)->nullable();
            $table->string('objek', 50)->nullable();
            $table->string('rincian', 50)->nullable();
            $table->string('sub_rincian', 50)->nullable();
            $table->unsignedBigInteger('id_unit_kerja')->nullable();
            $table->decimal('pagu_anggaran', 20, 2)->default(0)->comment('Pagu anggaran periode ini');
            $table->decimal('pagu_sebelumnya', 20, 2)->default(0)->comment('Pagu tahun sebelumnya untuk perbandingan');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['kode_rekening', 'is_active'], 'idx_rekening_active');
            $table->index(['id_unit_kerja', 'is_active'], 'idx_unit_rekening');
        });

        Schema::table('master_rekening_belanja', function (Blueprint $table) {
            try {
                $table->foreign('id_unit_kerja')->references('id_unit_kerja')->on('master_unit_kerja')->onDelete('set null');
            } catch (\Exception $e) {}
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_rekening_belanja');
    }
};