<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_report_sparepart', function (Blueprint $table) {
            $table->id('id_service_report_sparepart');
            $table->unsignedBigInteger('id_service_report');
            $table->string('nama_sparepart');
            $table->string('merk')->nullable();
            $table->string('nomor_seri')->nullable();
            $table->string('foto_path')->nullable();
            $table->timestamps();

            $table->foreign('id_service_report')
                ->references('id_service_report')
                ->on('service_report')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_report_sparepart');
    }
};
