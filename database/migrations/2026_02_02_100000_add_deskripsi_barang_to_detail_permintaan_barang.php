<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permintaan lainnya: id_data_barang nullable, deskripsi_barang untuk freetext.
     * Barang tidak masuk master_data_barang atau data_stock.
     */
    public function up(): void
    {
        Schema::table('detail_permintaan_barang', function (Blueprint $table) {
            $table->dropForeign(['id_data_barang']);
        });

        Schema::table('detail_permintaan_barang', function (Blueprint $table) {
            $table->unsignedBigInteger('id_data_barang')->nullable()->change();
            $table->string('deskripsi_barang', 500)->nullable()->after('id_data_barang');
            $table->foreign('id_data_barang')
                ->references('id_data_barang')
                ->on('master_data_barang')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('detail_permintaan_barang', function (Blueprint $table) {
            $table->dropForeign(['id_data_barang']);
        });

        Schema::table('detail_permintaan_barang', function (Blueprint $table) {
            $table->dropColumn('deskripsi_barang');
            $table->unsignedBigInteger('id_data_barang')->nullable(false)->change();
            $table->foreign('id_data_barang')
                ->references('id_data_barang')
                ->on('master_data_barang')
                ->onDelete('cascade');
        });
    }
};
