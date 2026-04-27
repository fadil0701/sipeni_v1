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
        Schema::table('retur_barang', function (Blueprint $table) {
            if (Schema::hasColumn('retur_barang', 'id_penerimaan')) {
                $table->dropForeign('retur_barang_id_penerimaan_foreign');
                $table->dropColumn('id_penerimaan');
            }

            if (Schema::hasColumn('retur_barang', 'id_distribusi')) {
                $table->dropForeign('retur_barang_id_distribusi_foreign');
                $table->dropColumn('id_distribusi');
            }

            if (Schema::hasColumn('retur_barang', 'keterangan')) {
                $table->dropColumn('keterangan');
            }
        });

        Schema::table('detail_retur_barang', function (Blueprint $table) {
            if (Schema::hasColumn('detail_retur_barang', 'keterangan')) {
                $table->dropColumn('keterangan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retur_barang', function (Blueprint $table) {
            if (! Schema::hasColumn('retur_barang', 'id_penerimaan')) {
                $table->foreignId('id_penerimaan')
                    ->nullable()
                    ->constrained('penerimaan_barang', 'id_penerimaan')
                    ->onDelete('cascade');
            }

            if (! Schema::hasColumn('retur_barang', 'id_distribusi')) {
                $table->foreignId('id_distribusi')
                    ->nullable()
                    ->constrained('transaksi_distribusi', 'id_distribusi')
                    ->onDelete('cascade');
            }

            if (! Schema::hasColumn('retur_barang', 'keterangan')) {
                $table->text('keterangan')->nullable();
            }
        });

        Schema::table('detail_retur_barang', function (Blueprint $table) {
            if (! Schema::hasColumn('detail_retur_barang', 'keterangan')) {
                $table->text('keterangan')->nullable();
            }
        });
    }
};

